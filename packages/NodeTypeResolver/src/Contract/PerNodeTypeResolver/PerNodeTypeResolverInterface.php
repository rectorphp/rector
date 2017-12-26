<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract\PerNodeTypeResolver;

use PhpParser\Node;

interface PerNodeTypeResolverInterface
{
    public function getNodeClass(): string;

    /**
     * @return string[]
     */
    public function resolve(Node $node): array;

    /**
     * If this resolves is subscribed to element that bears the type.
     * E.g. name itself does not, bug object, new instance, property and method does.
     */
    public function isPrimary(): bool;
}
