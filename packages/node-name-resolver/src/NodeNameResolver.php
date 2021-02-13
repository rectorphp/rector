<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\Regex\RegexPatternDetector;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NodeNameResolver
{
    /**
     * @var string
     */
    private const FILE = 'file';

    /**
     * @var NodeNameResolverInterface[]
     */
    private $nodeNameResolvers = [];

    /**
     * @var RegexPatternDetector
     */
    private $regexPatternDetector;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @param NodeNameResolverInterface[] $nodeNameResolvers
     */
    public function __construct(
        RegexPatternDetector $regexPatternDetector,
        BetterStandardPrinter $betterStandardPrinter,
        CurrentFileInfoProvider $currentFileInfoProvider,
        ClassNaming $classNaming,
        array $nodeNameResolvers = []
    ) {
        $this->regexPatternDetector = $regexPatternDetector;
        $this->nodeNameResolvers = $nodeNameResolvers;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->classNaming = $classNaming;
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

    /**
     * @param Node|Node[] $node
     */
    public function isName($node, string $name): bool
    {
        if ($node instanceof MethodCall) {
            $message = sprintf(
                'Name called on "%s" is not possible. Use $this->getName($node->name) instead',
                get_class($node)
            );
            throw new ShouldNotHappenException($message);
        }

        $nodes = is_array($node) ? $node : [$node];

        foreach ($nodes as $node) {
            if ($this->isSingleName($node, $name)) {
                return true;
            }
        }

        return false;
    }

    public function getName(Node $node): ?string
    {
        if ($node instanceof MethodCall || $node instanceof StaticCall) {
            if ($this->isCallOrIdentifier($node->name)) {
                return null;
            }

            $this->reportInvalidNodeForName($node);
        }

        foreach ($this->nodeNameResolvers as $nodeNameResolver) {
            if (! is_a($node, $nodeNameResolver->getNode(), true)) {
                continue;
            }

            return $nodeNameResolver->resolve($node);
        }

        // more complex
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
        $secondResolvedName = $this->getName($secondNode);
        if ($secondResolvedName === null) {
            return false;
        }

        return $this->isName($firstNode, $secondResolvedName);
    }

    /**
     * @param Name[]|Node[] $nodes
     * @return string[]
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

    public function isLocalPropertyFetchNamed(Node $node, string $name): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        if (! $this->isName($node->var, 'this')) {
            return false;
        }

        return $this->isName($node->name, $name);
    }

    public function isLocalStaticPropertyFetchNamed(Node $node, string $name): bool
    {
        if (! $node instanceof StaticPropertyFetch) {
            return false;
        }

        return $this->isName($node->name, $name);
    }

    /**
     * @param string[] $names
     */
    public function isFuncCallNames(Node $node, array $names): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        return $this->isNames($node, $names);
    }

    /**
     * Ends with ucname
     * Starts with adjective, e.g. (Post $firstPost, Post $secondPost)
     */
    public function endsWith(string $currentName, string $expectedName): bool
    {
        $suffixNamePattern = '#\w+' . ucfirst($expectedName) . '#';
        return (bool) Strings::match($currentName, $suffixNamePattern);
    }

    public function isLocalMethodCallNamed(Node $node, string $name): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if ($node->var instanceof StaticCall) {
            return false;
        }

        if ($node->var instanceof MethodCall) {
            return false;
        }

        if (! $this->isName($node->var, 'this')) {
            return false;
        }

        return $this->isName($node->name, $name);
    }

    /**
     * @param string[] $names
     */
    public function isLocalMethodCallsNamed(Node $node, array $names): bool
    {
        foreach ($names as $name) {
            if ($this->isLocalMethodCallNamed($node, $name)) {
                return true;
            }
        }

        return false;
    }

    public function isFuncCallName(Node $node, string $name): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        return $this->isName($node, $name);
    }

    public function isStaticCallNamed(Node $node, string $className, string $methodName): bool
    {
        if (! $node instanceof StaticCall) {
            return false;
        }

        if ($node->class instanceof New_) {
            if (! $this->isName($node->class->class, $className)) {
                return false;
            }
        } elseif (! $this->isName($node->class, $className)) {
            return false;
        }

        return $this->isName($node->name, $methodName);
    }

    /**
     * @param string[] $methodNames
     */
    public function isStaticCallsNamed(Node $node, string $className, array $methodNames): bool
    {
        foreach ($methodNames as $methodName) {
            if ($this->isStaticCallNamed($node, $className, $methodName)) {
                return true;
            }
        }

        return false;
    }

    public function isVariableName(Node $node, string $name): bool
    {
        if (! $node instanceof Variable) {
            return false;
        }

        return $this->isName($node, $name);
    }

    /**
     * @param string[] $desiredClassNames
     */
    public function isInClassNames(Node $node, array $desiredClassNames): bool
    {
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        foreach ($desiredClassNames as $desiredClassName) {
            if (is_a($className, $desiredClassName, true)) {
                return true;
            }
        }

        return false;
    }

    public function isInClassNamed(Node $node, string $desiredClassName): bool
    {
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        return is_a($className, $desiredClassName, true);
    }

    /**
     * @param string|Name|Identifier|ClassLike $name
     */
    public function getShortName($name): string
    {
        return $this->classNaming->getShortName($name);
    }

    private function isCallOrIdentifier(Node $node): bool
    {
        return StaticInstanceOf::isOneOf($node, [MethodCall::class, StaticCall::class, Identifier::class]);
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function reportInvalidNodeForName(Node $node): void
    {
        $message = sprintf('Pick more specific node than "%s", e.g. "$node->name"', get_class($node));

        $fileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
        if ($fileInfo instanceof SmartFileInfo) {
            $message .= PHP_EOL . PHP_EOL;
            $message .= sprintf(
                'Caused in "%s" file on line %d on code "%s"',
                $fileInfo->getRelativeFilePathFromCwd(),
                $node->getStartLine(),
                $this->betterStandardPrinter->print($node)
            );
        }

        $backtrace = debug_backtrace();
        $rectorBacktrace = $this->matchRectorBacktraceCall($backtrace);

        if ($rectorBacktrace) {
            // issues to find the file in prefixed
            if (file_exists($rectorBacktrace[self::FILE])) {
                $fileInfo = new SmartFileInfo($rectorBacktrace[self::FILE]);
                $fileAndLine = $fileInfo->getRelativeFilePathFromCwd() . ':' . $rectorBacktrace['line'];
            } else {
                $fileAndLine = $rectorBacktrace[self::FILE] . ':' . $rectorBacktrace['line'];
            }

            $message .= PHP_EOL . PHP_EOL;
            $message .= sprintf('Look at "%s"', $fileAndLine);
        }

        throw new ShouldNotHappenException($message);
    }

    /**
     * @param mixed[] $backtrace
     * @return mixed[]|null
     */
    private function matchRectorBacktraceCall(array $backtrace): ?array
    {
        foreach ($backtrace as $singleTrace) {
            if (! isset($singleTrace['object'])) {
                continue;
            }

            // match a Rector class
            if (! is_a($singleTrace['object'], RectorInterface::class)) {
                continue;
            }

            return $singleTrace;
        }

        return $backtrace[1] ?? null;
    }

    private function isSingleName(Node $node, string $name): bool
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
}
