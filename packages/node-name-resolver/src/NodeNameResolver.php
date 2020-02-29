<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\Regex\RegexPatternDetector;

final class NodeNameResolver
{
    /**
     * @var RegexPatternDetector
     */
    private $regexPatternDetector;

    /**
     * @var NodeNameResolverInterface[]
     */
    private $nodeNameResolvers = [];

    /**
     * @param NodeNameResolverInterface[] $nodeNameResolvers
     */
    public function __construct(RegexPatternDetector $regexPatternDetector, array $nodeNameResolvers = [])
    {
        $this->regexPatternDetector = $regexPatternDetector;
        $this->nodeNameResolvers = $nodeNameResolvers;
    }

    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names): bool
    {
        foreach ($names as $name) {
            if ($this->isName($node, $name)) {
                return true;
            }
        }

        return false;
    }

    public function isName(Node $node, string $name): bool
    {
        if ($node instanceof MethodCall) {
            // method call cannot have a name, only the variable or method name
            return false;
        }

        $resolvedName = $this->getName($node);
        if ($resolvedName === null) {
            return false;
        }

        if ($name === '') {
            return false;
        }

        // is probably regex pattern
        if ($this->regexPatternDetector->isRegexPattern($name)) {
            return (bool) Strings::match($resolvedName, $name);
        }

        // is probably fnmatch
        if (Strings::contains($name, '*')) {
            return fnmatch($name, $resolvedName, FNM_NOESCAPE);
        }

        // special case
        if ($name === 'Object') {
            return $name === $resolvedName;
        }

        return strtolower($resolvedName) === strtolower($name);
    }

    public function getName(Node $node): ?string
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            if ($node->name instanceof MethodCall || $node->name instanceof StaticCall) {
                return null;
            }

            throw new ShouldNotHappenException(sprintf('Pick more specific node than "%s"', get_class($node)));
        }

        foreach ($this->nodeNameResolvers as $nodeNameResolver) {
            if (! is_a($node, $nodeNameResolver->getNode(), true)) {
                continue;
            }

            return $nodeNameResolver->resolve($node);
        }

        // more complex
        if ($node instanceof Interface_ || $node instanceof Trait_) {
            return $this->resolveNamespacedNameAwareNode($node);
        }

        if (! property_exists($node, 'name')) {
            return null;
        }

        // unable to resolve
        if ($node->name instanceof Expr) {
            return null;
        }

        return (string) $node->name;
    }

    public function areNamesEqual(Node $firstNode, Node $secondNode): bool
    {
        return $this->getName($firstNode) === $this->getName($secondNode);
    }

    /**
     * @param Name[]|Node[] $nodes
     */
    public function getNames(array $nodes): array
    {
        $names = [];
        foreach ($nodes as $node) {
            $name = $this->getName($node);
            if (! is_string($name)) {
                throw new ShouldNotHappenException();
            }

            $names[] = $name;
        }

        return $names;
    }

    /**
     * @param Node[] $nodes
     */
    public function haveName(array $nodes, string $name): bool
    {
        foreach ($nodes as $node) {
            if (! $this->isName($node, $name)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param Interface_|Trait_ $classLike
     */
    private function resolveNamespacedNameAwareNode(ClassLike $classLike): ?string
    {
        if (isset($classLike->namespacedName)) {
            return $classLike->namespacedName->toString();
        }

        if ($classLike->name === null) {
            return null;
        }

        return $this->getName($classLike->name);
    }
}
