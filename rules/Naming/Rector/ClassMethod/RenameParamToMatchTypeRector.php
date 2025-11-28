<?php

declare (strict_types=1);
namespace Rector\Naming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Reflection\ClassReflection;
use Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Rector\Naming\Guard\BreakingVariableRenameGuard;
use Rector\Naming\Naming\ExpectedNameResolver;
use Rector\Naming\ParamRenamer\ParamRenamer;
use Rector\Naming\ValueObject\ParamRename;
use Rector\Naming\ValueObjectFactory\ParamRenameFactory;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\Skipper\FileSystem\PathNormalizer;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\RenameParamToMatchTypeRectorTest
 */
final class RenameParamToMatchTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BreakingVariableRenameGuard $breakingVariableRenameGuard;
    /**
     * @readonly
     */
    private ExpectedNameResolver $expectedNameResolver;
    /**
     * @readonly
     */
    private MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver;
    /**
     * @readonly
     */
    private ParamRenameFactory $paramRenameFactory;
    /**
     * @readonly
     */
    private ParamRenamer $paramRenamer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(BreakingVariableRenameGuard $breakingVariableRenameGuard, ExpectedNameResolver $expectedNameResolver, MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver, ParamRenameFactory $paramRenameFactory, ParamRenamer $paramRenamer, ReflectionResolver $reflectionResolver)
    {
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
        $this->paramRenameFactory = $paramRenameFactory;
        $this->paramRenamer = $paramRenamer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename param to match ClassType', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Apple $pie)
    {
        $food = $pie;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Apple $apple)
    {
        $food = $apple;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }
    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = \false;
        foreach ($node->params as $param) {
            // skip as array-like
            if ($param->variadic) {
                continue;
            }
            if ($param->type === null) {
                continue;
            }
            if ($this->skipExactType($param)) {
                return null;
            }
            if ($node instanceof ClassMethod && $this->shouldSkipClassMethodFromVendor($node)) {
                return null;
            }
            $expectedName = $this->expectedNameResolver->resolveForParamIfNotYet($param);
            if ($expectedName === null) {
                continue;
            }
            if ($this->shouldSkipParam($param, $expectedName, $node)) {
                continue;
            }
            $expectedName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($expectedName === null) {
                continue;
            }
            $paramRename = $this->paramRenameFactory->createFromResolvedExpectedName($node, $param, $expectedName);
            if (!$paramRename instanceof ParamRename) {
                continue;
            }
            $this->paramRenamer->rename($paramRename);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * Avoid renaming parameters of a class method, that is located in /vendor,
     * to keep name matching for named arguments.
     */
    private function shouldSkipClassMethodFromVendor(ClassMethod $classMethod): bool
    {
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $ancestorClassReflections = array_filter($classReflection->getAncestors(), fn(ClassReflection $ancestorClassReflection): bool => $classReflection->getName() !== $ancestorClassReflection->getName());
        $methodName = $this->getName($classMethod);
        foreach ($ancestorClassReflections as $ancestorClassReflection) {
            // internal
            if ($ancestorClassReflection->getFileName() === null) {
                continue;
            }
            if (!$ancestorClassReflection->hasNativeMethod($methodName)) {
                continue;
            }
            $path = PathNormalizer::normalize($ancestorClassReflection->getFileName());
            if (strpos($path, '/vendor/') !== \false) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $classMethod
     */
    private function shouldSkipParam(Param $param, string $expectedName, $classMethod): bool
    {
        /** @var string $paramName */
        $paramName = $this->getName($param);
        if ($this->breakingVariableRenameGuard->shouldSkipParam($paramName, $expectedName, $classMethod, $param)) {
            return \true;
        }
        if (!$classMethod instanceof ClassMethod) {
            return \false;
        }
        // promoted property
        if (!$this->isName($classMethod, MethodName::CONSTRUCT)) {
            return \false;
        }
        return $param->isPromoted();
    }
    /**
     * Skip couple quote vague types, that could be named explicitly on purpose.
     */
    private function skipExactType(Param $param): bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        return $this->isName($param->type, Node::class);
    }
}
