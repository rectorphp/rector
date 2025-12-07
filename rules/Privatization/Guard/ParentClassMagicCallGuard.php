<?php

declare (strict_types=1);
namespace Rector\Privatization\Guard;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
final class ParentClassMagicCallGuard
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * To speed up analysis
     * @var string[]
     */
    private const KNOWN_DYNAMIC_CALL_CLASSES = [Standard::class, PrettyPrinterAbstract::class];
    /**
     * @var array<string, bool>
     */
    private array $cachedContainsByClassName = [];
    public function __construct(NodeNameResolver $nodeNameResolver, AstResolver $astResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->astResolver = $astResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        foreach (self::KNOWN_DYNAMIC_CALL_CLASSES as $knownDynamicCallClass) {
            $this->cachedContainsByClassName[$knownDynamicCallClass] = \true;
        }
    }
    /**
     * E.g. parent class has $this->{$magicName} call that might call the protected method
     * If we make it private, it will break the code
     */
    public function containsParentClassMagicCall(Class_ $class): bool
    {
        if (!$class->extends instanceof Name) {
            return \false;
        }
        // cache as heavy AST parsing here
        $className = $this->nodeNameResolver->getName($class);
        if (isset($this->cachedContainsByClassName[$className])) {
            return $this->cachedContainsByClassName[$className];
        }
        $parentClassName = $this->nodeNameResolver->getName($class->extends);
        if (isset($this->cachedContainsByClassName[$parentClassName])) {
            return $this->cachedContainsByClassName[$parentClassName];
        }
        $parentClass = $this->astResolver->resolveClassFromName($parentClassName);
        if (!$parentClass instanceof Class_) {
            $this->cachedContainsByClassName[$parentClassName] = \false;
            return \false;
        }
        foreach ($parentClass->getMethods() as $classMethod) {
            if ($classMethod->isAbstract()) {
                continue;
            }
            /** @var MethodCall[] $methodCalls */
            $methodCalls = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, MethodCall::class);
            foreach ($methodCalls as $methodCall) {
                if ($methodCall->name instanceof Expr) {
                    $this->cachedContainsByClassName[$parentClassName] = \true;
                    return \true;
                }
            }
        }
        return $this->containsParentClassMagicCall($parentClass);
    }
}
