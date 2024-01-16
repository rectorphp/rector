<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Symfony\ValueObject\ExtensionKeyAndConfiguration;
final class SymfonyClosureExtensionMatcher
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, ValueResolver $valueResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
    }
    public function match(Closure $closure) : ?ExtensionKeyAndConfiguration
    {
        if (\count($closure->stmts) > 1) {
            $extensionNames = $this->resolveExtensionNames($closure);
            if (\count($extensionNames) > 2) {
                return null;
            }
            // warn use early about it, to avoid silent skip
            $errorMessage = \sprintf('Split extensions "%s" to multiple separated files first', \implode('", "', $extensionNames));
            throw new ShouldNotHappenException($errorMessage);
        }
        // must be exactly single line
        if (\count($closure->stmts) !== 1) {
            return null;
        }
        $onlyStmt = $closure->stmts[0];
        if (!$onlyStmt instanceof Expression) {
            return null;
        }
        if (!$onlyStmt->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $onlyStmt->expr;
        $args = $methodCall->getArgs();
        $extensionKey = $this->matchExtensionName($methodCall);
        if (!\is_string($extensionKey)) {
            return null;
        }
        $secondArg = $args[1];
        if (!$secondArg->value instanceof Array_) {
            return null;
        }
        return new ExtensionKeyAndConfiguration($extensionKey, $secondArg->value);
    }
    private function matchExtensionName(MethodCall $methodCall) : ?string
    {
        if (!$this->nodeNameResolver->isName($methodCall->name, 'extension')) {
            return null;
        }
        $args = $methodCall->getArgs();
        $firstArg = $args[0];
        return $this->valueResolver->getValue($firstArg->value);
    }
    /**
     * @return string[]
     */
    private function resolveExtensionNames(Closure $closure) : array
    {
        $extensionNames = [];
        foreach ($closure->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $expr = $stmt->expr;
            if (!$expr instanceof MethodCall) {
                continue;
            }
            $extensionName = $this->matchExtensionName($expr);
            if (!\is_string($extensionName)) {
                continue;
            }
            $extensionNames[] = $extensionName;
        }
        return $extensionNames;
    }
}
