<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Contract\Rector\RectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\Regex\RegexPatternDetector;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
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
     * @param NodeNameResolverInterface[] $nodeNameResolvers
     */
    public function __construct(
        RegexPatternDetector $regexPatternDetector,
        BetterStandardPrinter $betterStandardPrinter,
        CurrentFileInfoProvider $currentFileInfoProvider,
        array $nodeNameResolvers = []
    ) {
        $this->regexPatternDetector = $regexPatternDetector;
        $this->nodeNameResolvers = $nodeNameResolvers;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
        $this->betterStandardPrinter = $betterStandardPrinter;
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
            if ($node->name instanceof MethodCall || $node->name instanceof StaticCall || $node->name instanceof Identifier) {
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
            $message .= sprintf('Look at %s', $fileAndLine);
        }

        throw new ShouldNotHappenException($message);
    }

    /**
     * @param Interface_|Trait_ $classLike
     */
    private function resolveNamespacedNameAwareNode(ClassLike $classLike): ?string
    {
        if (property_exists($classLike, 'namespacedName')) {
            return $classLike->namespacedName->toString();
        }

        if ($classLike->name === null) {
            return null;
        }

        return $this->getName($classLike->name);
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
}
