<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Ramsey\Uuid\UuidInterface;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\JMS\SerializerTypeTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\Doctrine\Tests\Rector\Property\AddUuidAnnotationsToIdPropertyRector\AddUuidAnnotationsToIdPropertyRectorTest
 */
final class AddUuidAnnotationsToIdPropertyRector extends AbstractRector
{
    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(PhpDocTypeChanger $phpDocTypeChanger)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add uuid annotations to $id property', [
            new CodeSample(
            <<<'CODE_SAMPLE'
use Doctrine\ORM\Attributes as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Type("int")
     */
    public $id;
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
use Doctrine\ORM\Attributes as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     * @ORM\Id
     * @ORM\Column(type="uuid_binary")
     * @Serializer\Type("string")
     */
    public $id;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (! $phpDocInfo->hasByType(AbstractDoctrineTagValueNode::class)) {
            return null;
        }

        if (! $this->isName($node, 'id')) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $fullyQualifiedObjectType = new FullyQualifiedObjectType(UuidInterface::class);

        $this->phpDocTypeChanger->changeVarType($phpDocInfo, $fullyQualifiedObjectType);

        $phpDocInfo->removeByType(GeneratedValueTagValueNode::class);
        $this->changeColumnTypeToUuidBinary($phpDocInfo);
        $this->changeSerializerTypeToString($phpDocInfo);

        return $node;
    }

    private function changeColumnTypeToUuidBinary(PhpDocInfo $phpDocInfo): void
    {
        $columnTagValueNode = $phpDocInfo->getByType(ColumnTagValueNode::class);
        if (! $columnTagValueNode instanceof ColumnTagValueNode) {
            return;
        }

        $columnTagValueNode->changeType('uuid_binary');
    }

    private function changeSerializerTypeToString(PhpDocInfo $phpDocInfo): void
    {
        $serializerTypeTagValueNode = $phpDocInfo->getByType(SerializerTypeTagValueNode::class);
        if (! $serializerTypeTagValueNode instanceof SerializerTypeTagValueNode) {
            return;
        }

        if ($serializerTypeTagValueNode->getName() === 'string') {
            return;
        }

        $serializerTypeTagValueNode->changeName('string');
    }
}
