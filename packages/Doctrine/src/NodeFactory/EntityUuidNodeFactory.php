<?php

declare(strict_types=1);

namespace Rector\Doctrine\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\Doctrine\ValueObject\DoctrineClass;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\CustomIdGeneratorTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\GeneratedValueTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\IdTagValueNode;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;

final class EntityUuidNodeFactory
{
    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var string
     */
    private $doctrineUuidGeneratorClass;

    public function __construct(
        string $doctrineUuidGeneratorClass,
        DocBlockManipulator $docBlockManipulator,
        BuilderFactory $builderFactory
    ) {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->builderFactory = $builderFactory;
        $this->doctrineUuidGeneratorClass = $doctrineUuidGeneratorClass;
    }

    public function createTemporaryUuidProperty(): Property
    {
        $uuidProperty = $this->builderFactory->property('uuid')
            ->makePrivate()
            ->getNode();

        $this->decoratePropertyWithUuidAnnotations($uuidProperty, true, false);

        return $uuidProperty;
    }

    /**
     * Creates: "$this->uid = \Ramsey\Uuid\Uuid::uuid4();"
     */
    public function createUuidPropertyDefaultValueAssign(): Expression
    {
        $thisUuidPropertyFetch = new PropertyFetch(new Variable('this'), 'uuid');
        $uuid4StaticCall = new StaticCall(new FullyQualified('Ramsey\Uuid\Uuid'), 'uuid4');

        $assign = new Assign($thisUuidPropertyFetch, $uuid4StaticCall);

        return new Expression($assign);
    }

    public function createConstructorWithUuidPropertyDefaultValueAssign(): ClassMethod
    {
        $classMethodBuilder = $this->builderFactory->method('__construct');
        $classMethodBuilder->makePublic();

        $assign = $this->createUuidPropertyDefaultValueAssign();
        $classMethodBuilder->addStmt($assign);

        return $classMethodBuilder->getNode();
    }

    private function clearVarAndOrmAnnotations(Node $node): void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return;
        }

        $clearedDocCommentText = Strings::replace($docComment->getText(), '#^(\s+)\*(\s+)\@(var|ORM)(.*?)$#ms');

        $node->setDocComment(new Doc($clearedDocCommentText));
    }

    private function decoratePropertyWithUuidAnnotations(Property $property, bool $isNullable, bool $isId): void
    {
        $this->clearVarAndOrmAnnotations($property);
        $this->replaceIntSerializerTypeWithString($property);

        // add @var
        $this->docBlockManipulator->addTag($property, $this->createVarTagUuidInterface());

        if ($isId) {
            // add @ORM\Id
            $this->docBlockManipulator->addTag($property, $this->createIdTag());
        }

        $this->docBlockManipulator->addTag($property, $this->createUuidColumnTag($isNullable));

        if ($isId) {
            $this->docBlockManipulator->addTag($property, $this->createGeneratedValueTag());
            $this->docBlockManipulator->addTag($property, $this->createCustomIdGeneratorTag());
        }
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
            '#(\@Serializer\\\\Type\(")(int)("\))#',
            '$1string$3'
        );

        $node->setDocComment(new Doc($stringTypeText));
    }

    private function createVarTagUuidInterface(): PhpDocTagNode
    {
        $varTagValueNode = new VarTagValueNode(new IdentifierTypeNode(
            '\\' . DoctrineClass::RAMSEY_UUID_INTERFACE
        ), '', '');

        return new PhpDocTagNode('@var', $varTagValueNode);
    }

    private function createIdTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(IdTagValueNode::SHORT_NAME, new IdTagValueNode());
    }

    private function createUuidColumnTag(bool $isNullable): PhpDocTagNode
    {
        $columnTagValueNode = new ColumnTagValueNode(
            null,
            'uuid_binary',
            null,
            null,
            null,
            true,
            $isNullable ? true : null
        );

        return new PhpDocTagNode($columnTagValueNode::SHORT_NAME, $columnTagValueNode);
    }

    private function createGeneratedValueTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(GeneratedValueTagValueNode::SHORT_NAME, new GeneratedValueTagValueNode('CUSTOM'));
    }

    private function createCustomIdGeneratorTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(
            CustomIdGeneratorTagValueNode::SHORT_NAME,
            new CustomIdGeneratorTagValueNode($this->doctrineUuidGeneratorClass)
        );
    }
}
