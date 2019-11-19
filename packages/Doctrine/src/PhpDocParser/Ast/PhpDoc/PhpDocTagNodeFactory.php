<?php

declare(strict_types=1);

namespace Rector\Doctrine\PhpDocParser\Ast\PhpDoc;

use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Ramsey\Uuid\UuidInterface;
use Rector\BetterPhpDocParser\Attributes\Ast\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\GeneratedValueTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\IdTagValueNode;
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

    public function createVarTagUuidInterface(): PhpDocTagNode
    {
        $identifierTypeNode = new IdentifierTypeNode('\\' . UuidInterface::class);
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
}
