<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\Php81\Enum\NameNullToStrictNullFunctionMap;
use Rector\Php81\NodeManipulator\NullToStrictStringIntConverter;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector\NullToStrictStringFuncCallArgRectorTest
 */
final class NullToStrictStringFuncCallArgRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private NullToStrictStringIntConverter $nullToStrictStringIntConverter;
    public function __construct(ReflectionResolver $reflectionResolver, NullToStrictStringIntConverter $nullToStrictStringIntConverter)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->nullToStrictStringIntConverter = $nullToStrictStringIntConverter;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change null to strict string defined function call args', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_split("#a#", null);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        preg_split("#a#", '');
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        $args = $node->getArgs();
        $positions = $this->resolveStringPositions($node, $args, $scope);
        if ($positions === []) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        $isTrait = $classReflection instanceof ClassReflection && $classReflection->isTrait();
        $functionReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$functionReflection instanceof FunctionReflection) {
            return null;
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($functionReflection, $node, $scope);
        $isChanged = \false;
        foreach ($positions as $position) {
            $result = $this->nullToStrictStringIntConverter->convertIfNull($node, $args, (int) $position, $isTrait, $scope, $parametersAcceptor);
            if ($result instanceof Node) {
                $node = $result;
                $isChanged = \true;
            }
        }
        if ($isChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_NULL_ARG_IN_STRING_FUNCTION;
    }
    /**
     * @param Arg[] $args
     * @return int[]
     */
    private function resolveStringPositions(FuncCall $funcCall, array $args, Scope $scope): array
    {
        $positions = [];
        $functionName = $this->getName($funcCall);
        $argNames = NameNullToStrictNullFunctionMap::FUNCTION_TO_PARAM_NAMES[$functionName] ?? [];
        $excludedArgNames = [];
        foreach ($args as $position => $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->isNames($arg->name, $argNames)) {
                continue;
            }
            $excludedArgNames[] = $arg->name->toString();
            $positions[] = $position;
        }
        $functionReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($funcCall);
        if (!$functionReflection instanceof NativeFunctionReflection) {
            return $positions;
        }
        $parametersAcceptor = ParametersAcceptorSelectorVariantsWrapper::select($functionReflection, $funcCall, $scope);
        $functionName = $functionReflection->getName();
        $argNames = NameNullToStrictNullFunctionMap::FUNCTION_TO_PARAM_NAMES[$functionName];
        foreach ($parametersAcceptor->getParameters() as $position => $parameterReflection) {
            if (in_array($parameterReflection->getName(), $argNames, \true) && !in_array($parameterReflection->getName(), $excludedArgNames, \true)) {
                $positions[] = $position;
            }
        }
        return $positions;
    }
    private function shouldSkip(FuncCall $funcCall): bool
    {
        $functionNames = array_keys(NameNullToStrictNullFunctionMap::FUNCTION_TO_PARAM_NAMES);
        if (!$this->isNames($funcCall, $functionNames)) {
            return \true;
        }
        return $funcCall->isFirstClassCallable();
    }
}
