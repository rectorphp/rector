<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\Guard\ParamTypeAddGuard;
use Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\ParamTypeByMethodCallTypeRectorTest
 */
final class ParamTypeByMethodCallTypeRector extends AbstractScopeAwareRector
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher
     */
    private $callerParamMatcher;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\Guard\ParamTypeAddGuard
     */
    private $paramTypeAddGuard;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Mapper\PhpParserNodeMapper
     */
    private $phpParserNodeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(CallerParamMatcher $callerParamMatcher, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, ParamTypeAddGuard $paramTypeAddGuard, BetterNodeFinder $betterNodeFinder, PhpParserNodeMapper $phpParserNodeMapper, StaticTypeMapper $staticTypeMapper, TypeFactory $typeFactory)
    {
        $this->callerParamMatcher = $callerParamMatcher;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->paramTypeAddGuard = $paramTypeAddGuard;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpParserNodeMapper = $phpParserNodeMapper;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeFactory = $typeFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change param type based on passed method call type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(string $name)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go($value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(string $name)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go(string $value)
    {
        $this->someTypedService->run($value);
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
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            /** @var array<StaticCall|MethodCall|FuncCall> $callers */
            $callers = $this->betterNodeFinder->findInstancesOf($classMethod, [StaticCall::class, MethodCall::class, FuncCall::class]);
            $hasClassMethodChanged = $this->refactorClassMethod($classMethod, $callers, $scope);
            if ($hasClassMethodChanged) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        if ($classMethod->params === []) {
            return \true;
        }
        return $this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod);
    }
    private function shouldSkipParam(Param $param, ClassMethod $classMethod) : bool
    {
        // already has type, skip
        if ($param->type !== null) {
            return \true;
        }
        if ($param->variadic) {
            return \true;
        }
        return !$this->paramTypeAddGuard->isLegal($param, $classMethod);
    }
    /**
     * @param array<StaticCall|MethodCall|FuncCall> $callers
     */
    private function refactorClassMethod(ClassMethod $classMethod, array $callers, Scope $scope) : bool
    {
        $hasChanged = \false;
        foreach ($classMethod->params as $param) {
            if ($this->shouldSkipParam($param, $classMethod)) {
                continue;
            }
            $paramTypes = [];
            foreach ($callers as $caller) {
                $matchCallParam = $this->callerParamMatcher->matchCallParam($caller, $param, $scope);
                // nothing to do with param, continue
                if (!$matchCallParam instanceof Param) {
                    continue;
                }
                $paramType = $this->callerParamMatcher->matchCallParamType($param, $matchCallParam);
                if (!$paramType instanceof Node) {
                    $paramTypes = [];
                    break;
                }
                $paramTypes[] = $this->phpParserNodeMapper->mapToPHPStanType($paramType);
                $hasChanged = \true;
            }
            if ($paramTypes === []) {
                continue;
            }
            $type = $this->typeFactory->createMixedPassedOrUnionType($paramTypes);
            $paramNodeType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PARAM);
            if ($paramNodeType instanceof Node) {
                $param->type = $paramNodeType;
            }
        }
        return $hasChanged;
    }
}
