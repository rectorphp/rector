<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\DeadCode\NodeAnalyzer\IsClassMethodUsedAnalyzer;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\Symfony\TypeAnalyzer\ControllerAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\ClassMethod\RemoveUnusedRequestParamRector\RemoveUnusedRequestParamRectorTest
 */
final class RemoveUnusedRequestParamRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ControllerAnalyzer $controllerAnalyzer;
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
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer;
    public function __construct(ControllerAnalyzer $controllerAnalyzer, ReflectionResolver $reflectionResolver, ClassChildAnalyzer $classChildAnalyzer, BetterNodeFinder $betterNodeFinder, IsClassMethodUsedAnalyzer $isClassMethodUsedAnalyzer)
    {
        $this->controllerAnalyzer = $controllerAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->classChildAnalyzer = $classChildAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->isClassMethodUsedAnalyzer = $isClassMethodUsedAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused $request parameter from controller action', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function run(Request $request, int $id)
    {
        echo $id;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

final class SomeController extends Controller
{
    public function run(int $id)
    {
        echo $id;
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
        if (!$this->controllerAnalyzer->isInsideController($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPublic()) {
                continue;
            }
            if ($classMethod->isAbstract() || $this->hasAbstractParentClassMethod($classMethod)) {
                continue;
            }
            if ($classMethod->getParams() === []) {
                continue;
            }
            // skip empty method
            if ($classMethod->stmts === null) {
                continue;
            }
            foreach ($classMethod->getParams() as $paramPosition => $param) {
                if (!$param->type instanceof Node) {
                    continue;
                }
                if (!$this->isObjectType($param->type, new ObjectType('Symfony\\Component\\HttpFoundation\\Request'))) {
                    continue;
                }
                /** @var string $requestParamName */
                $requestParamName = $this->getName($param);
                // we have request param here
                $requestVariable = $this->betterNodeFinder->findVariableOfName($classMethod->stmts, $requestParamName);
                // is variable used?
                if ($requestVariable instanceof Variable) {
                    continue 2;
                }
                $scope = ScopeFetcher::fetch($node);
                if ($this->isClassMethodUsedAnalyzer->isClassMethodUsed($node, $classMethod, $scope)) {
                    continue 2;
                }
                unset($classMethod->params[$paramPosition]);
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function hasAbstractParentClassMethod(ClassMethod $classMethod) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->classChildAnalyzer->hasAbstractParentClassMethod($classReflection, $this->getName($classMethod));
    }
}
