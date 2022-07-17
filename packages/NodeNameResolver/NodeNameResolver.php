<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\CallAnalyzer;
use Rector\Core\Util\StringUtils;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\Error\InvalidNameNodeReporter;
use Rector\NodeNameResolver\Regex\RegexPatternDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class NodeNameResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\Regex\RegexPatternDetector
     */
    private $regexPatternDetector;
    /**
     * @readonly
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\Error\InvalidNameNodeReporter
     */
    private $invalidNameNodeReporter;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    /**
     * @var NodeNameResolverInterface[]
     * @readonly
     */
    private $nodeNameResolvers = [];
    /**
     * @param NodeNameResolverInterface[] $nodeNameResolvers
     */
    public function __construct(RegexPatternDetector $regexPatternDetector, ClassNaming $classNaming, InvalidNameNodeReporter $invalidNameNodeReporter, CallAnalyzer $callAnalyzer, array $nodeNameResolvers = [])
    {
        $this->regexPatternDetector = $regexPatternDetector;
        $this->classNaming = $classNaming;
        $this->invalidNameNodeReporter = $invalidNameNodeReporter;
        $this->callAnalyzer = $callAnalyzer;
        $this->nodeNameResolvers = $nodeNameResolvers;
    }
    /**
     * @param string[] $names
     */
    public function isNames(Node $node, array $names) : bool
    {
        foreach ($names as $name) {
            if ($this->isName($node, $name)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node|mixed[] $node
     */
    public function isName($node, string $name) : bool
    {
        if ($node instanceof MethodCall) {
            return \false;
        }
        if ($node instanceof StaticCall) {
            return \false;
        }
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            if ($this->isSingleName($node, $name)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @api
     */
    public function isCaseSensitiveName(Node $node, string $name) : bool
    {
        if ($name === '') {
            return \false;
        }
        if ($node instanceof MethodCall) {
            return \false;
        }
        if ($node instanceof StaticCall) {
            return \false;
        }
        $resolvedName = $this->getName($node);
        if ($resolvedName === null) {
            return \false;
        }
        return $name === $resolvedName;
    }
    /**
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
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            if ($this->isCallOrIdentifier($node->name)) {
                return null;
            }
            $this->invalidNameNodeReporter->reportInvalidNodeForName($node);
        }
        foreach ($this->nodeNameResolvers as $nodeNameResolver) {
            if (!\is_a($node, $nodeNameResolver->getNode(), \true)) {
                continue;
            }
            return $nodeNameResolver->resolve($node);
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
    public function areNamesEqual(Node $firstNode, Node $secondNode) : bool
    {
        $secondResolvedName = $this->getName($secondNode);
        if ($secondResolvedName === null) {
            return \false;
        }
        return $this->isName($firstNode, $secondResolvedName);
    }
    /**
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
     * Ends with ucname
     * Starts with adjective, e.g. (Post $firstPost, Post $secondPost)
     */
    public function endsWith(string $currentName, string $expectedName) : bool
    {
        $suffixNamePattern = '#\\w+' . \ucfirst($expectedName) . '#';
        return StringUtils::isMatch($currentName, $suffixNamePattern);
    }
    /**
     * @param string|\PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\Stmt\ClassLike $name
     */
    public function getShortName($name) : string
    {
        return $this->classNaming->getShortName($name);
    }
    /**
     * @param array<string, string> $renameMap
     */
    public function matchNameFromMap(Node $node, array $renameMap) : ?string
    {
        $name = $this->getName($node);
        return $renameMap[$name] ?? null;
    }
    public function isStringName(string $resolvedName, string $desiredName) : bool
    {
        if ($desiredName === '') {
            return \false;
        }
        // is probably regex pattern
        if ($this->regexPatternDetector->isRegexPattern($desiredName)) {
            return StringUtils::isMatch($resolvedName, $desiredName);
        }
        // is probably fnmatch
        if (\strpos($desiredName, '*') !== \false) {
            return \fnmatch($desiredName, $resolvedName, \FNM_NOESCAPE);
        }
        // special case
        if ($desiredName === 'Object') {
            return $desiredName === $resolvedName;
        }
        return \strtolower($resolvedName) === \strtolower($desiredName);
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
        if ($node instanceof MethodCall) {
            // method call cannot have a name, only the variable or method name
            return \false;
        }
        $resolvedName = $this->getName($node);
        if ($resolvedName === null) {
            return \false;
        }
        return $this->isStringName($resolvedName, $desiredName);
    }
}
