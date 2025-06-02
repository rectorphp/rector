<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ClassReflection;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveArgumentFromDefaultParentCallRector\RemoveArgumentFromDefaultParentCallRectorTest
 */
final class RemoveArgumentFromDefaultParentCallRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    public function __construct(ReflectionResolver $reflectionResolver, ArgsAnalyzer $argsAnalyzer, ValueResolver $valueResolver, ExprAnalyzer $exprAnalyzer)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->argsAnalyzer = $argsAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove default argument from parent call', [new CodeSample(<<<'CODE_SAMPLE'
class SomeParent
{
    public function __construct(array $param = [])
    {
    }
}

class ChildClass extends SomeParent
{
    public function __construct(string $differentParam)
    {
        init($differentParam);

        parent::__construct([]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeParent
{
    public function __construct(array $param = [])
    {
    }
}

class ChildClass extends SomeParent
{
    public function __construct(string $differentParam)
    {
        init($differentParam);

        parent::__construct();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if (!$node->extends instanceof FullyQualified) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $ancestors = \array_filter($classReflection->getAncestors(), fn(ClassReflection $ancestorClassReflection): bool => $classReflection->isClass() && $ancestorClassReflection->getName() !== $classReflection->getName());
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->isPrivate()) {
                continue;
            }
            if ($classMethod->isAbstract()) {
                continue;
            }
            foreach ((array) $classMethod->stmts as $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }
                if (!$stmt->expr instanceof StaticCall) {
                    continue;
                }
                if (!$this->isName($stmt->expr->class, 'parent')) {
                    continue;
                }
                if ($stmt->expr->isFirstClassCallable()) {
                    continue;
                }
                $args = $stmt->expr->getArgs();
                if ($args === []) {
                    continue;
                }
                if ($this->argsAnalyzer->hasNamedArg($args)) {
                    continue;
                }
                $methodName = $this->getName($stmt->expr->name);
                if ($methodName === null) {
                    continue;
                }
                foreach ($ancestors as $ancestor) {
                    $nativeClassReflection = $ancestor->getNativeReflection();
                    if (!$nativeClassReflection->hasMethod($methodName)) {
                        continue;
                    }
                    $method = $nativeClassReflection->getMethod($methodName);
                    $parameters = $method->getParameters();
                    $totalParameters = \count($parameters);
                    $justChanged = \false;
                    for ($index = $totalParameters - 1; $index >= 0; --$index) {
                        if (!$parameters[$index]->isDefaultValueAvailable()) {
                            break;
                        }
                        // already passed
                        if (!isset($args[$index])) {
                            break;
                        }
                        // only literal values
                        if ($this->exprAnalyzer->isDynamicExpr($args[$index]->value)) {
                            break;
                        }
                        $defaultValue = $parameters[$index]->getDefaultValue();
                        if ($defaultValue === $this->valueResolver->getValue($args[$index]->value)) {
                            unset($args[$index]);
                            $hasChanged = \true;
                            $justChanged = \true;
                        }
                    }
                    if ($justChanged) {
                        $stmt->expr->args = \array_values($args);
                    }
                    break;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
