<?php declare(strict_types=1);

namespace Rector\Rector;

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
    public function setNameResolver(NameResolver $nameResolver): void
    {
        $this->nameResolver = $nameResolver;
    }

    public function isName(Node $node, string $name): bool
    {
        return $this->nameResolver->isName($node, $name);
    }

    public function areNamesEqual(Node $firstNode, Node $secondNode): bool
    {
        return $this->getName($firstNode) === $this->getName($secondNode);
    }

    public function isNameInsensitive(Node $node, string $name): bool
    {
        return strtolower((string) $this->getName($node)) === strtolower($name);
    }

    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        return in_array($this->getName($node), $names, true);
    }

    public function getName(Node $node): ?string
    {
        return $this->nameResolver->resolve($node);
    }
}
