<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait NameResolverTrait
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @required
     */
    public function autowireNameResolverTrait(NodeNameResolver $nodeNameResolver, ClassNaming $classNaming): void
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classNaming = $classNaming;
    }

    public function isName(Node $node, string $name): bool
    {
        return $this->nodeNameResolver->isName($node, $name);
    }

    public function areNamesEqual(Node $firstNode, Node $secondNode): bool
    {
        return $this->nodeNameResolver->areNamesEqual($firstNode, $secondNode);
    }

    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        return $this->nodeNameResolver->isNames($node, $names);
    }

    public function getName(Node $node): ?string
    {
        return $this->nodeNameResolver->getName($node);
    }

    /**
     * @param string|Name|Identifier $name
     */
    protected function getShortName($name): string
    {
        return $this->classNaming->getShortName($name);
    }
}
