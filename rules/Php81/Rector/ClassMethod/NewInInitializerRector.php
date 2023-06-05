<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\Php81\NodeAnalyzer\CoalesePropertyAssignMatcher;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/new_in_initializers
 *
 * @see \Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\NewInInitializerRectorTest
 */
final class NewInInitializerRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer
     */
    private $classChildAnalyzer;
    /**
     * @readonly
     * @var \Rector\Php81\NodeAnalyzer\CoalesePropertyAssignMatcher
     */
    private $coalesePropertyAssignMatcher;
    public function __construct(ReflectionResolver $reflectionResolver, ClassChildAnalyzer $classChildAnalyzer, CoalesePropertyAssignMatcher $coalesePropertyAssignMatcher)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->coalesePropertyAssignMatcher = $coalesePropertyAssignMatcher;
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
                $coalesce = $this->coalesePropertyAssignMatcher->matchCoalesceAssignsToLocalPropertyNamed($stmt, $paramName);
                if (!$coalesce instanceof Coalesce) {
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
        $methodName = $this->nodeNameResolver->getName($classMethod);
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
        return \array_filter($classMethod->params, static function (Param $param) : bool {
            return $param->type instanceof NullableType;
        });
    }
}
