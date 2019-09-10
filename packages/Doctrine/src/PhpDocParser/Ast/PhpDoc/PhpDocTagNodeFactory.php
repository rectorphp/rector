<?php declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser\Ast\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\Doctrine\Uuid\JoinTableNameResolver;
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
     * @var JoinTableNameResolver
     */
    private $joinTableNameResolver;

    public function __construct(string $doctrineUuidGeneratorClass, JoinTableNameResolver $joinTableNameResolver)
    {
        $this->doctrineUuidGeneratorClass = $doctrineUuidGeneratorClass;
        $this->joinTableNameResolver = $joinTableNameResolver;
    }

    public function createVarTagUuidInterface(): PhpDocTagNode
    {
        $identifierTypeNode = new IdentifierTypeNode('\\' . DoctrineClass::RAMSEY_UUID_INTERFACE);
        $varTagValueNode = new VarTagValueNode($identifierTypeNode, '', '');

        return new PhpDocTagNode('@var', $varTagValueNode);
    }

    public function createIdTag(): PhpDocTagNode
    {
        return new SpacelessPhpDocTagNode(IdTagValueNode::SHORT_NAME, new IdTagValueNode());
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

        return new SpacelessPhpDocTagNode($columnTagValueNode::SHORT_NAME, $columnTagValueNode);
    }

    public function createGeneratedValueTag(): PhpDocTagNode
    {
        return new SpacelessPhpDocTagNode(GeneratedValueTagValueNode::SHORT_NAME, new GeneratedValueTagValueNode(
            'CUSTOM'
        ));
    }

    public function createCustomIdGeneratorTag(): PhpDocTagNode
    {
        return new SpacelessPhpDocTagNode(
            CustomIdGeneratorTagValueNode::SHORT_NAME,
            new CustomIdGeneratorTagValueNode($this->doctrineUuidGeneratorClass)
        );
    }

    public function createJoinTableTagNode(Property $property): PhpDocTagNode
    {
        $uuidJoinTable = $this->joinTableNameResolver->resolveManyToManyUuidTableNameForProperty($property);

        $joinTableTagValueNode = new JoinTableTagValueNode(
            $uuidJoinTable,
            null,
            [new JoinColumnTagValueNode(null, 'uuid')],
            [new JoinColumnTagValueNode(null, 'uuid')]
        );

        return new SpacelessPhpDocTagNode(JoinTableTagValueNode::SHORT_NAME, $joinTableTagValueNode);
    }

    public function createJoinColumnTagNode(): PhpDocTagNode
    {
        $joinColumnTagValueNode = new JoinColumnTagValueNode(null, 'uuid', null, false);

        return new SpacelessPhpDocTagNode(JoinColumnTagValueNode::SHORT_NAME, $joinColumnTagValueNode);
    }
}
