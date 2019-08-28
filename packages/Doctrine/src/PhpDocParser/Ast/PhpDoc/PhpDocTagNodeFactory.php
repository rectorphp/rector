<?php declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser\Ast\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\AttributeAwarePhpDocTagNode;
use Rector\Doctrine\Uuid\UuidTableNameResolver;
use Rector\Doctrine\ValueObject\DoctrineClass;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\ColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\CustomIdGeneratorTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\GeneratedValueTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\IdTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinColumnTagValueNode;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_\JoinTableTagValueNode;

final class PhpDocTagNodeFactory
{
    /**
     * @var string
     */
    private $doctrineUuidGeneratorClass;

    /**
     * @var UuidTableNameResolver
     */
    private $uuidTableNameResolver;

    public function __construct(string $doctrineUuidGeneratorClass, UuidTableNameResolver $uuidTableNameResolver)
    {
        $this->doctrineUuidGeneratorClass = $doctrineUuidGeneratorClass;
        $this->uuidTableNameResolver = $uuidTableNameResolver;
    }

    public function createVarTagUuidInterface(): PhpDocTagNode
    {
        $varTagValueNode = new VarTagValueNode(new IdentifierTypeNode(
            '\\' . DoctrineClass::RAMSEY_UUID_INTERFACE
        ), '', '');

        return new PhpDocTagNode('@var', $varTagValueNode);
    }

    public function createIdTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(IdTagValueNode::SHORT_NAME, new IdTagValueNode());
    }

    public function createUuidColumnTag(bool $isNullable): PhpDocTagNode
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

    public function createGeneratedValueTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(GeneratedValueTagValueNode::SHORT_NAME, new GeneratedValueTagValueNode('CUSTOM'));
    }

    public function createCustomIdGeneratorTag(): PhpDocTagNode
    {
        return new PhpDocTagNode(
            CustomIdGeneratorTagValueNode::SHORT_NAME,
            new CustomIdGeneratorTagValueNode($this->doctrineUuidGeneratorClass)
        );
    }

    public function createJoinTableTagNode(Property $property): PhpDocTagNode
    {
        $joinTableName = $this->uuidTableNameResolver->resolveManyToManyTableNameForProperty($property);

        $joinTableTagValueNode = new JoinTableTagValueNode($joinTableName, null, [
            new JoinColumnTagValueNode(null, 'uuid'),
        ], [new JoinColumnTagValueNode(null, 'uuid')]);

        return new AttributeAwarePhpDocTagNode(JoinTableTagValueNode::SHORT_NAME, $joinTableTagValueNode);
    }

    public function createJoinColumnTagNode(): PhpDocTagNode
    {
        $joinColumnTagValueNode = new JoinColumnTagValueNode(null, 'uuid', null, false);

        return new AttributeAwarePhpDocTagNode(JoinColumnTagValueNode::SHORT_NAME, $joinColumnTagValueNode);
    }
}
