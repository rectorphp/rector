<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpParser;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\PhpParser\PhpParserNodeMapperInterface;
use Rector\PHPStan\Type\FullyQualifiedObjectType;

final class FullyQualifiedNodeMapper implements PhpParserNodeMapperInterface
{
    public function getNodeType(): string
    {
        return FullyQualified::class;
    }

    /**
     * @param FullyQualified $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        return new FullyQualifiedObjectType($node->toString());
    }
}
