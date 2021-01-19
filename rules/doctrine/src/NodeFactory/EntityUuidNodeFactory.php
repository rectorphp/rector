<?php

declare(strict_types=1);

namespace Rector\Doctrine\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Ramsey\Uuid\Uuid;
use Rector\BetterPhpDocParser\Contract\Doctrine\DoctrineTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;

final class EntityUuidNodeFactory
{
    /**
     * @var PhpDocTagNodeFactory
     */
    private $phpDocTagNodeFactory;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    public function __construct(
        NodeFactory $nodeFactory,
        PhpDocTagNodeFactory $phpDocTagNodeFactory,
        PhpDocInfoFactory $phpDocInfoFactory
    ) {
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
        $this->nodeFactory = $nodeFactory;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }

    public function createTemporaryUuidProperty(): Property
    {
        $uuidProperty = $this->nodeFactory->createPrivateProperty('uuid');

        $this->decoratePropertyWithUuidAnnotations($uuidProperty, true, false);

        return $uuidProperty;
    }

    /**
     * Creates:
     * $this->uid = \Ramsey\Uuid\Uuid::uuid4();
     */
    public function createUuidPropertyDefaultValueAssign(string $uuidVariableName): Expression
    {
        $thisUuidPropertyFetch = new PropertyFetch(new Variable('this'), $uuidVariableName);
        $uuid4StaticCall = $this->nodeFactory->createStaticCall(Uuid::class, 'uuid4');

        $assign = new Assign($thisUuidPropertyFetch, $uuid4StaticCall);

        return new Expression($assign);
    }

    private function decoratePropertyWithUuidAnnotations(Property $property, bool $isNullable, bool $isId): void
    {
        $this->clearVarAndOrmAnnotations($property);
        $this->replaceIntSerializerTypeWithString($property);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        // add @var
        $attributeAwareVarTagValueNode = $this->phpDocTagNodeFactory->createUuidInterfaceVarTagValueNode();
        $phpDocInfo->addTagValueNode($attributeAwareVarTagValueNode);

        if ($isId) {
            // add @ORM\Id
            $idTagValueNode = new IdTagValueNode([]);
            $phpDocInfo->addTagValueNodeWithShortName($idTagValueNode);
        }

        $columnTagValueNode = $this->phpDocTagNodeFactory->createUuidColumnTagValueNode($isNullable);
        $phpDocInfo->addTagValueNodeWithShortName($columnTagValueNode);

        if (! $isId) {
            return;
        }

        $generatedValueTagValueNode = new GeneratedValueTagValueNode([
            'strategy' => 'CUSTOM',
        ]);
        $phpDocInfo->addTagValueNodeWithShortName($generatedValueTagValueNode);
    }

    private function clearVarAndOrmAnnotations(Property $property): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $phpDocInfo->removeByType(VarTagValueNode::class);
        $phpDocInfo->removeByType(DoctrineTagNodeInterface::class);
    }

    /**
     * See https://github.com/ramsey/uuid-doctrine/issues/50#issuecomment-348123520.
     */
    private function replaceIntSerializerTypeWithString(Property $property): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);

        $serializerTypeTagValueNode = $phpDocInfo->getByType(SerializerTypeTagValueNode::class);
        if (! $serializerTypeTagValueNode instanceof SerializerTypeTagValueNode) {
            return;
        }

        $serializerTypeTagValueNode->replaceName('int', 'string');
    }
}
