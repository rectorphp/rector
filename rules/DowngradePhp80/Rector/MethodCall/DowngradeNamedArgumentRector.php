<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector\DowngradeNamedArgumentRectorTest
 */
final class DowngradeNamedArgumentRector extends AbstractRector
{
    public function __construct(
        private ReflectionResolver $reflectionResolver
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, New_::class];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove named argument',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private function execute(?array $a = null, ?array $b = null)
    {
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute(a: [[$name ?? 0 => $attributes]]);
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    private function execute(?array $a = null, ?array $b = null)
    {
    }

    public function run(string $name = null, array $attributes = [])
    {
        $this->execute([[$name ?? 0 => $attributes]]);
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $args = $node->args;
        if ($this->shouldSkip($args)) {
            return null;
        }

        $this->removeNamedArguments($node, $args);
        return $node;
    }

    /**
     * @param Arg[] $args
     */
    private function removeNamedArguments(MethodCall | StaticCall | New_ $node, array $args): ?Node
    {
        if ($node instanceof New_) {
            $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($node);
            if (! $methodReflection instanceof MethodReflection) {
                return null;
            }

            return $this->processRemoveNamedArgument($methodReflection, $node, $args);
        }

        $functionLikeReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if ($functionLikeReflection === null) {
            return null;
        }

        return $this->processRemoveNamedArgument($functionLikeReflection, $node, $args);
    }

    /**
     * @param MethodCall|StaticCall|New_ $node
     * @param Arg[] $args
     */
    private function processRemoveNamedArgument(
        MethodReflection | FunctionReflection $reflection,
        Node $node,
        array $args
    ): MethodCall | StaticCall | New_ {
        /** @var Arg[] $newArgs */
        $newArgs = [];
        $keyParam = 0;

        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($reflection->getVariants());
        $parameterReflections = $parametersAcceptor->getParameters();

        foreach ($parameterReflections as $keyParam => $parameterReflection) {
            $paramName = $parameterReflection->getName();

            foreach ($args as $arg) {
                /** @var string|null $argName */
                $argName = $this->getName($arg);

                if ($paramName === $argName) {
                    $newArgs[$keyParam] = new Arg(
                        $arg->value,
                        $arg->byRef,
                        $arg->unpack,
                        $arg->getAttributes(),
                        null
                    );
                }
            }
        }

        $this->replacePreviousArgs($node, $parameterReflections, $keyParam, $newArgs);
        return $node;
    }

    /**
     * @param ParameterReflection[] $parameterReflections
     * @param Arg[] $newArgs
     */
    private function replacePreviousArgs(
        MethodCall | StaticCall | New_ $node,
        array $parameterReflections,
        int $keyParam,
        array $newArgs
    ): void {
        for ($i = $keyParam - 1; $i >= 0; --$i) {
            $parameterReflection = $parameterReflections[$i];
            if ($parameterReflection->getDefaultValue() === null) {
                continue;
            }

            $defaultValue = $this->mapPHPStanTypeToExpr($parameterReflection->getDefaultValue());
            if (! $defaultValue instanceof Expr) {
                continue;
            }

            if (isset($newArgs[$i])) {
                continue;
            }

            $newArgs[$i] = new Arg($defaultValue);
        }

        $countNewArgs = count($newArgs);
        for ($i = 0; $i < $countNewArgs; ++$i) {
            $node->args[$i] = $newArgs[$i];
        }
    }

    /**
     * @param Arg[] $args
     */
    private function shouldSkip(array $args): bool
    {
        if ($args === []) {
            return true;
        }

        foreach ($args as $arg) {
            if ($arg->name instanceof Identifier) {
                return false;
            }
        }

        return true;
    }

    private function mapPHPStanTypeToExpr(?Type $type): ?Expr
    {
        if ($type === null) {
            return null;
        }

        return new ConstFetch(new Name($type->describe(VerbosityLevel::value())));
    }
}
