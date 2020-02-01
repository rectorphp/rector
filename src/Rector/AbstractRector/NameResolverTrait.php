<?php

declare(strict_types=1);

namespace Rector\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\CodingStyle\Naming\ClassNaming;
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
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @required
     */
    public function autowireNameResolverTrait(NameResolver $nameResolver, ClassNaming $classNaming): void
    {
        $this->nameResolver = $nameResolver;
        $this->classNaming = $classNaming;
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

    /**
     * @param string|Name|Identifier $name
     */
    protected function getShortName($name): string
    {
        return $this->classNaming->getShortName($name);
    }
}
