<?php

declare (strict_types=1);
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
final class DowngradeNamedArgumentRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\New_::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove named argument', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
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
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $node
     */
    private function removeNamedArguments($node, array $args) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\New_) {
            $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromNew($node);
            if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
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
     * @param Arg[] $args
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $reflection
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_
     */
    private function processRemoveNamedArgument($reflection, $node, array $args)
    {
        /** @var Arg[] $newArgs */
        $newArgs = [];
        $keyParam = 0;
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($reflection->getVariants());
        $parameterReflections = $parametersAcceptor->getParameters();
        foreach ($parameterReflections as $keyParam => $parameterReflection) {
            $paramName = $parameterReflection->getName();
            foreach ($args as $arg) {
                /** @var string|null $argName */
                $argName = $this->getName($arg);
                if ($paramName === $argName) {
                    $newArgs[$keyParam] = new \PhpParser\Node\Arg($arg->value, $arg->byRef, $arg->unpack, $arg->getAttributes(), null);
                }
            }
        }
        $this->replacePreviousArgs($node, $parameterReflections, $keyParam, $newArgs);
        return $node;
    }
    /**
     * @param ParameterReflection[] $parameterReflections
     * @param Arg[] $newArgs
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $node
     */
    private function replacePreviousArgs($node, array $parameterReflections, int $keyParam, array $newArgs) : void
    {
        for ($i = $keyParam - 1; $i >= 0; --$i) {
            $parameterReflection = $parameterReflections[$i];
            if ($parameterReflection->getDefaultValue() === null) {
                continue;
            }
            $defaultValue = $this->mapPHPStanTypeToExpr($parameterReflection->getDefaultValue());
            if (!$defaultValue instanceof \PhpParser\Node\Expr) {
                continue;
            }
            if (isset($newArgs[$i])) {
                continue;
            }
            $newArgs[$i] = new \PhpParser\Node\Arg($defaultValue);
        }
        $countNewArgs = \count($newArgs);
        for ($i = 0; $i < $countNewArgs; ++$i) {
            $node->args[$i] = $newArgs[$i];
        }
    }
    /**
     * @param Arg[] $args
     */
    private function shouldSkip(array $args) : bool
    {
        if ($args === []) {
            return \true;
        }
        foreach ($args as $arg) {
            if ($arg->name instanceof \PhpParser\Node\Identifier) {
                return \false;
            }
        }
        return \true;
    }
    private function mapPHPStanTypeToExpr(?\PHPStan\Type\Type $type) : ?\PhpParser\Node\Expr
    {
        if ($type === null) {
            return null;
        }
        return new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name($type->describe(\PHPStan\Type\VerbosityLevel::value())));
    }
}
