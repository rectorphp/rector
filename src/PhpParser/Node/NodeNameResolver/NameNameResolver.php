<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\NameResolver\NodeNameResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NameNameResolver implements NodeNameResolverInterface
{
    public function getNode(): string
    {
        return Name::class;
    }

    /**
     * @param Name $node
     */
    public function resolve(Node $node): ?string
    {
        $resolvedName = $node->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($resolvedName instanceof FullyQualified) {
            return $resolvedName->toString();
        }

        return $node->toString();
    }
}
