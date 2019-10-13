<?php

declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node;
use Rector\PhpParser\Node\Resolver\NameResolver;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NameResolverTrait
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @required
     */
    public function autowireNameResolverTrait(NameResolver $nameResolver): void
    {
        $this->nameResolver = $nameResolver;
    }

    public function isName(Node $node, string $name): bool
    {
        return $this->nameResolver->isName($node, $name);
    }

    public function areNamesEqual(Node $firstNode, Node $secondNode): bool
    {
        return $this->nameResolver->areNamesEqual($firstNode, $secondNode);
    }

    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        return $this->nameResolver->isNames($node, $names);
    }

    public function getName(Node $node): ?string
    {
        return $this->nameResolver->getName($node);
    }
}
