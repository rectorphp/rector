<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Const_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\ValueObject\MethodName;
final class NodeNameResolver
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    /**
     * @var NodeNameResolverInterface[]
     * @readonly
     */
    private $nodeNameResolvers = [];
    /**
     * Used to check if a string might contain a regex or fnmatch pattern
     */
    private const REGEX_WILDCARD_CHARS = ['*', '#', '~', '/'];
    /**
     * @var array<string, NodeNameResolverInterface|null>
     */
    private $nodeNameResolversByClass = [];
    /**
     * @param NodeNameResolverInterface[] $nodeNameResolvers
     */
    public function __construct(ClassNaming $classNaming, CallAnalyzer $callAnalyzer, iterable $nodeNameResolvers = [])
    {
        $this->classNaming = $classNaming;
        $this->callAnalyzer = $callAnalyzer;
        $this->nodeNameResolvers = $nodeNameResolvers;
    }
    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names) : bool
    {
        $nodeName = $this->getName($node);
        if ($nodeName === null) {
            return \false;
        }
        foreach ($names as $name) {
            if ($this->isStringName($nodeName, $name)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Node|Node[] $node
     * @param MethodName::*|string $name
     */
    public function isName($node, string $name) : bool
    {
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            if ($this->isSingleName($node, $name)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Some nodes have always-known string name. This makes PHPStan smarter.
     * @see https://phpstan.org/writing-php-code/phpdoc-types#conditional-return-types
     *
     * @return ($node is Param ? string :
     *  ($node is ClassMethod ? string :
     *  ($node is Property ? string :
     *  ($node is PropertyProperty ? string :
     *  ($node is Trait_ ? string :
     *  ($node is Interface_ ? string :
     *  ($node is Const_ ? string :
     *  ($node is Node\Const_ ? string :
     *  ($node is Name ? string :
     *      string|null )))))))))
     * @param \PhpParser\Node|string $node
     */
    public function getName($node) : ?string
    {
        if (\is_string($node)) {
            return $node;
        }
        // useful for looped imported names
        $namespacedName = $node->getAttribute(AttributeKey::NAMESPACED_NAME);
        if (\is_string($namespacedName)) {
            return $namespacedName;
        }
        if (($node instanceof MethodCall || $node instanceof StaticCall || $node instanceof NullsafeMethodCall) && $this->isCallOrIdentifier($node->name)) {
            return null;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $resolvedName = $this->resolveNodeName($node, $scope);
        if ($resolvedName !== null) {
            return $resolvedName;
        }
        // more complex
        if (!\property_exists($node, 'name')) {
            return null;
        }
        // unable to resolve
        if ($node->name instanceof Expr) {
            return null;
        }
        return (string) $node->name;
    }
    /**
     * @api
     */
    public function areNamesEqual(Node $firstNode, Node $secondNode) : bool
    {
        $secondResolvedName = $this->getName($secondNode);
        if ($secondResolvedName === null) {
            return \false;
        }
        return $this->isName($firstNode, $secondResolvedName);
    }
    /**
     * @api
     *
     * @param Name[]|Node[] $nodes
     * @return string[]
     */
    public function getNames(array $nodes) : array
    {
        $names = [];
        foreach ($nodes as $node) {
            $name = $this->getName($node);
            if (!\is_string($name)) {
                throw new ShouldNotHappenException();
            }
            $names[] = $name;
        }
        return $names;
    }
    /**
     * @param string|\PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\Stmt\ClassLike $name
     */
    public function getShortName($name) : string
    {
        return $this->classNaming->getShortName($name);
    }
    public function isStringName(string $resolvedName, string $desiredName) : bool
    {
        if ($desiredName === '') {
            return \false;
        }
        // special case
        if ($desiredName === 'Object') {
            return $desiredName === $resolvedName;
        }
        if (\strcasecmp($resolvedName, $desiredName) === 0) {
            return \true;
        }
        foreach (self::REGEX_WILDCARD_CHARS as $char) {
            if (\strpos($desiredName, $char) !== \false) {
                throw new ShouldNotHappenException('Matching of regular expressions is no longer supported. Use $this->getName() and compare with e.g. str_ends_with() or str_starts_with() instead.');
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Identifier $node
     */
    private function isCallOrIdentifier($node) : bool
    {
        if ($node instanceof Expr) {
            return $this->callAnalyzer->isObjectCall($node);
        }
        return \true;
    }
    private function isSingleName(Node $node, string $desiredName) : bool
    {
        if ($node instanceof CallLike && !$node instanceof FuncCall) {
            // method call cannot have a name, only the variable or method name
            return \false;
        }
        $resolvedName = $this->getName($node);
        if ($resolvedName === null) {
            return \false;
        }
        return $this->isStringName($resolvedName, $desiredName);
    }
    private function resolveNodeName(Node $node, ?Scope $scope) : ?string
    {
        $nodeClass = \get_class($node);
        if (\array_key_exists($nodeClass, $this->nodeNameResolversByClass)) {
            $resolver = $this->nodeNameResolversByClass[$nodeClass];
            if ($resolver instanceof NodeNameResolverInterface) {
                return $resolver->resolve($node, $scope);
            }
            return null;
        }
        foreach ($this->nodeNameResolvers as $nodeNameResolver) {
            if (!\is_a($node, $nodeNameResolver->getNode(), \true)) {
                continue;
            }
            $this->nodeNameResolversByClass[$nodeClass] = $nodeNameResolver;
            return $nodeNameResolver->resolve($node, $scope);
        }
        $this->nodeNameResolversByClass[$nodeClass] = null;
        return null;
    }
}
