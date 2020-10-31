<?php

declare(strict_types=1);

namespace Rector\Doctrine\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Ramsey\Uuid\Uuid;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\IdTagValueNode;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Doctrine\PhpDocParser\Ast\PhpDoc\PhpDocTagNodeFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class EntityUuidNodeFactory
{
    /**
     * @var string
     * @see https://regex101.com/r/vQ8f2v/1
     */
    private const SERIALIZER_SHORT_ANNOTATION_REGEX = '#(\@Serializer\\\\Type\(")(int)("\))#';

    /**
     * @var string
     * @see https://regex101.com/r/AkLsy1/1
     */
    private const ORM_VAR_DOC_LINE_REGEX = '#^(\s+)\*(\s+)\@(var|ORM)(.*?)$#ms';

    /**
     * @var PhpDocTagNodeFactory
     */
    private $phpDocTagNodeFactory;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory, PhpDocTagNodeFactory $phpDocTagNodeFactory)
    {
        $this->phpDocTagNodeFactory = $phpDocTagNodeFactory;
        $this->nodeFactory = $nodeFactory;
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
        $uuid4StaticCall = new StaticCall(new FullyQualified(Uuid::class), 'uuid4');

        $assign = new Assign($thisUuidPropertyFetch, $uuid4StaticCall);

        return new Expression($assign);
    }

    private function decoratePropertyWithUuidAnnotations(Property $property, bool $isNullable, bool $isId): void
    {
        $this->clearVarAndOrmAnnotations($property);
        $this->replaceIntSerializerTypeWithString($property);

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

        // add @var
        $attributeAwareVarTagValueNode = $this->phpDocTagNodeFactory->createUuidInterfaceVarTagValueNode();
        $phpDocInfo->addTagValueNode($attributeAwareVarTagValueNode);

        if ($isId) {
            // add @ORM\Id
            $phpDocInfo->addTagValueNodeWithShortName(new IdTagValueNode([]));
        }

        $columnTagValueNode = $this->phpDocTagNodeFactory->createUuidColumnTagValueNode($isNullable);
        $phpDocInfo->addTagValueNodeWithShortName($columnTagValueNode);

        if ($isId) {
            $generatedValueTagValueNode = new GeneratedValueTagValueNode([
                'strategy' => 'CUSTOM',
            ]);
            $phpDocInfo->addTagValueNodeWithShortName($generatedValueTagValueNode);
        }
    }

    private function clearVarAndOrmAnnotations(Node $node): void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return;
        }

        $clearedDocCommentText = Strings::replace($docComment->getText(), self::ORM_VAR_DOC_LINE_REGEX);
        $node->setDocComment(new Doc($clearedDocCommentText));
    }

    /**
     * See https://github.com/ramsey/uuid-doctrine/issues/50#issuecomment-348123520.
     */
    private function replaceIntSerializerTypeWithString(Node $node): void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return;
        }

        $stringTypeText = Strings::replace(
            $docComment->getText(),
            self::SERIALIZER_SHORT_ANNOTATION_REGEX,
            '$1string$3'
        );

        $node->setDocComment(new Doc($stringTypeText));
    }
}
