<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\CallLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\CallLike\AddNameToBooleanArgumentRector\AddNameToBooleanArgumentRectorTest
 */
final class AddNameToBooleanArgumentRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ReflectionResolver $reflectionResolver, ValueResolver $valueResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add parameter names to boolean arguments.', [new CodeSample(<<<'CODE_SAMPLE'
in_array($value, $array, true);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
in_array($value, $array, strict: true);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [CallLike::class];
    }
    /**
     * @param CallLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$reflection instanceof FunctionReflection && !$reflection instanceof MethodReflection) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $args = $node->getArgs();
        $parameters = ParametersAcceptorSelectorVariantsWrapper::select($reflection, $node, $scope)->getParameters();
        $position = $this->resolveFirstPositionToName($args, $parameters);
        if ($position === null) {
            return null;
        }
        $wasChanged = \false;
        for ($i = $position; $i < count($args); ++$i) {
            $arg = $args[$i];
            if ($arg->name instanceof Identifier) {
                continue;
            }
            $parameterReflection = $this->resolveParameterReflection($arg, $i, $parameters);
            if (!$parameterReflection instanceof ParameterReflection) {
                return null;
            }
            $arg->name = new Identifier($parameterReflection->getName());
            $wasChanged = \true;
        }
        if (!$wasChanged) {
            return null;
        }
        return $node;
    }
    private function shouldSkip(CallLike $callLike): bool
    {
        if ($callLike->isFirstClassCallable()) {
            return \true;
        }
        $args = $callLike->getArgs();
        if ($args === []) {
            return \true;
        }
        foreach ($args as $arg) {
            if ($arg->unpack) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Arg[] $args
     * @param ParameterReflection[] $parameters
     */
    private function resolveFirstPositionToName(array $args, array $parameters): ?int
    {
        foreach ($args as $position => $arg) {
            if ($arg->name instanceof Identifier) {
                continue;
            }
            if (!$this->valueResolver->isTrueOrFalse($arg->value)) {
                continue;
            }
            if ($this->canNameArgsFromPosition($args, $parameters, $position)) {
                return $position;
            }
        }
        return null;
    }
    /**
     * @param Arg[] $args
     * @param ParameterReflection[] $parameters
     */
    private function canNameArgsFromPosition(array $args, array $parameters, int $position): bool
    {
        $count = count($args);
        for ($i = $position; $i < $count; ++$i) {
            $arg = $args[$i];
            if ($arg->name instanceof Identifier) {
                continue;
            }
            $parameterReflection = $this->resolveParameterReflection($arg, $i, $parameters);
            if (!$parameterReflection instanceof ParameterReflection) {
                return \false;
            }
            if ($parameterReflection->isVariadic()) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * @param ParameterReflection[] $parameters
     */
    private function resolveParameterReflection(Arg $arg, int $position, array $parameters): ?ParameterReflection
    {
        if ($arg->name instanceof Identifier) {
            foreach ($parameters as $parameter) {
                if ($parameter->getName() === $arg->name->toString()) {
                    return $parameter;
                }
            }
            return null;
        }
        $parameter = $parameters[$position] ?? null;
        if ($parameter instanceof ParameterReflection) {
            return $parameter;
        }
        $lastParameter = end($parameters);
        if ($lastParameter instanceof ParameterReflection && $lastParameter->isVariadic()) {
            return $lastParameter;
        }
        return null;
    }
}
