<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;

final class NameNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType(): string
    {
        return Name::class;
    }

    /**
     * @param Name $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        $name = $node->toString();

        if (ClassExistenceStaticHelper::doesClassLikeExist($name)) {
            return new FullyQualifiedObjectType($node->toString());
        }

        return new MixedType();
    }
}
