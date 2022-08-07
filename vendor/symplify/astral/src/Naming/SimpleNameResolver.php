<?php

declare (strict_types=1);
namespace RectorPrefix202208\Symplify\Astral\Naming;

use RectorPrefix202208\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Property;
use RectorPrefix202208\Symplify\Astral\Contract\NodeNameResolverInterface;
/**
 * @see \Symplify\Astral\Tests\Naming\SimpleNameResolverTest
 */
final class SimpleNameResolver
{
    /**
     * @see https://regex101.com/r/ChpDsj/1
     * @var string
     */
    public const ANONYMOUS_CLASS_REGEX = '#^AnonymousClass[\\w+]#';
    /**
     * @var NodeNameResolverInterface[]
     */
    private $nodeNameResolvers;
    /**
     * @param NodeNameResolverInterface[] $nodeNameResolvers
     */
    public function __construct(array $nodeNameResolvers)
    {
        $this->nodeNameResolvers = $nodeNameResolvers;
    }
    /**
     * @param \PhpParser\Node|string $node
     */
    public function getName($node) : ?string
    {
        if (\is_string($node)) {
            return $node;
        }
        foreach ($this->nodeNameResolvers as $nodeNameResolver) {
            if (!$nodeNameResolver->match($node)) {
                continue;
            }
            return $nodeNameResolver->resolve($node);
        }
        if ($node instanceof ClassConstFetch && $this->isName($node->name, 'class')) {
            return $this->getName($node->class);
        }
        if ($node instanceof Property) {
            $propertyProperty = $node->props[0];
            return $this->getName($propertyProperty->name);
        }
        if ($node instanceof Variable) {
            return $this->getName($node->name);
        }
        return null;
    }
    /**
     * @param string|\PhpParser\Node $node
     */
    public function isName($node, string $desiredName) : bool
    {
        $name = $this->getName($node);
        if ($name === null) {
            return \false;
        }
        if (\strpos($desiredName, '*') !== \false) {
            return \fnmatch($desiredName, $name);
        }
        return $name === $desiredName;
    }
    /**
     * @api
     */
    public function areNamesEqual(Node $firstNode, Node $secondNode) : bool
    {
        $firstName = $this->getName($firstNode);
        if ($firstName === null) {
            return \false;
        }
        return $this->isName($secondNode, $firstName);
    }
    /**
     * @api
     */
    public function isNameMatch(Node $node, string $desiredNameRegex) : bool
    {
        $name = $this->getName($node);
        if ($name === null) {
            return \false;
        }
        return (bool) Strings::match($name, $desiredNameRegex);
    }
}
