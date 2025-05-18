<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\NodeManipulator\StmtsManipulator;
use Rector\Php81\NodeAnalyzer\CoalescePropertyAssignMatcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\NewInInitializerRectorTest
 */
final class NewInInitializerRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ClassChildAnalyzer $classChildAnalyzer;
    /**
     * @readonly
     */
    private CoalescePropertyAssignMatcher $coalescePropertyAssignMatcher;
    /**
     * @readonly
     */
    private StmtsManipulator $stmtsManipulator;
    public function __construct(ReflectionResolver $reflectionResolver, ClassChildAnalyzer $classChildAnalyzer, CoalescePropertyAssignMatcher $coalescePropertyAssignMatcher, StmtsManipulator $stmtsManipulator)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->coalescePropertyAssignMatcher = $coalescePropertyAssignMatcher;
        $this->stmtsManipulator = $stmtsManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace property declaration of new state with direct new', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private Logger $logger;

    public function __construct(
        ?Logger $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private Logger $logger = new NullLogger,
    ) {
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
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null || $node->stmts === []) {
            return null;
        }
        if ($node->isAbstract() || $node->isAnonymous()) {
            return null;
        }
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        $params = $this->resolveParams($constructClassMethod);
        if ($params === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ((array) $constructClassMethod->stmts as $key => $stmt) {
            foreach ($params as $param) {
                $paramName = $this->getName($param);
                $coalesce = $this->coalescePropertyAssignMatcher->matchCoalesceAssignsToLocalPropertyNamed($stmt, $paramName);
                if (!$coalesce instanceof Coalesce) {
                    continue;
                }
                if ($this->stmtsManipulator->isVariableUsedInNextStmt($constructClassMethod, $key + 1, $paramName)) {
                    continue;
                }
                /** @var NullableType $currentParamType */
                $currentParamType = $param->type;
                $param->type = $currentParamType->type;
                $param->default = $coalesce->right;
                unset($constructClassMethod->stmts[$key]);
                $this->processPropertyPromotion($node, $param, $paramName);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NEW_INITIALIZERS;
    }
    /**
     * @return Param[]
     */
    private function resolveParams(ClassMethod $classMethod) : array
    {
        $params = $this->matchConstructorParams($classMethod);
        if ($params === []) {
            return [];
        }
        if ($this->isOverrideAbstractMethod($classMethod)) {
            return [];
        }
        return $params;
    }
    private function isOverrideAbstractMethod(ClassMethod $classMethod) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        $methodName = $this->getName($classMethod);
        return $classReflection instanceof ClassReflection && $this->classChildAnalyzer->hasAbstractParentClassMethod($classReflection, $methodName);
    }
    private function processPropertyPromotion(Class_ $class, Param $param, string $paramName) : void
    {
        foreach ($class->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            $property = $stmt;
            if (!$this->isName($stmt, $paramName)) {
                continue;
            }
            $param->flags = $property->flags;
            $param->attrGroups = \array_merge($property->attrGroups, $param->attrGroups);
            unset($class->stmts[$key]);
        }
    }
    /**
     * @return Param[]
     */
    private function matchConstructorParams(ClassMethod $classMethod) : array
    {
        // skip empty constructor assigns, as we need those here
        if ($classMethod->stmts === null || $classMethod->stmts === []) {
            return [];
        }
        $params = \array_filter($classMethod->params, static fn(Param $param): bool => $param->type instanceof NullableType);
        if ($params === []) {
            return $params;
        }
        $totalParams = \count($classMethod->params);
        foreach (\array_keys($params) as $key) {
            for ($iteration = $key + 1; $iteration < $totalParams; ++$iteration) {
                if (isset($classMethod->params[$iteration]) && !$classMethod->params[$iteration]->default instanceof Expr) {
                    return [];
                }
            }
        }
        return $params;
    }
}
