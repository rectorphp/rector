<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeFunctionReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\Php81\Enum\NameNullToStrictNullFunctionMap;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector\NullToStrictStringFuncCallArgRectorTest
 */
final class NullToStrictStringFuncCallArgRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
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
        $isChanged = \false;
        foreach ($positions as $position) {
            if (!isset($args[$position])) {
                continue;
            }
            $activeArg = $args[$position];
            if (!$this->getType($activeArg->value)->isNull()->yes()) {
                continue;
            }
            $activeArg->value = new String_('');
            $isChanged = \true;
        }
        if ($isChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_81;
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
