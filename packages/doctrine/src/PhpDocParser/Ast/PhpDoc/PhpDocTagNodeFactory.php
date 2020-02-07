<?php

declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser\Ast\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Ramsey\Uuid\UuidInterface;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareVarTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\Doctrine\Uuid\JoinTableNameResolver;

final class PhpDocTagNodeFactory
{
    /**
     * @var JoinTableNameResolver
     */
    private $joinTableNameResolver;

    public function __construct(JoinTableNameResolver $joinTableNameResolver)
    {
        $this->joinTableNameResolver = $joinTableNameResolver;
    }

    public function createVarTagIntValueNode(): AttributeAwareVarTagValueNode
    {
        return $this->createVarTagValueNodeWithType(new IdentifierTypeNode('int'));
    }

    public function createUuidInterfaceVarTagValueNode(): AttributeAwareVarTagValueNode
    {
        $identifierTypeNode = new IdentifierTypeNode('\\' . UuidInterface::class);

        return $this->createVarTagValueNodeWithType($identifierTypeNode);
    }

    public function createIdColumnTagValueNode(): ColumnTagValueNode
    {
        return new ColumnTagValueNode(null, 'integer', null, null, null, null, null);
    }

    public function createUuidColumnTagValueNode(bool $isNullable): ColumnTagValueNode
    {
        return new ColumnTagValueNode(null, 'uuid_binary', null, null, null, true, $isNullable ? true : null);
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

    public function createJoinColumnTagNode(bool $isNullable): PhpDocTagNode
    {
        $joinColumnTagValueNode = new JoinColumnTagValueNode(null, 'uuid', null, $isNullable);

        return new SpacelessPhpDocTagNode(JoinColumnTagValueNode::SHORT_NAME, $joinColumnTagValueNode);
    }

    private function createVarTagValueNodeWithType(TypeNode $typeNode): AttributeAwareVarTagValueNode
    {
        return new AttributeAwareVarTagValueNode($typeNode, '', '');
    }
}
