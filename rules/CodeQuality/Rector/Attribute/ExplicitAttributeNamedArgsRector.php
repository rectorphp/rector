<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Attribute;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Attribute\ExplicitAttributeNamedArgsRector\ExplicitAttributeNamedArgsRectorTest
 */
final class ExplicitAttributeNamedArgsRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Convert positional arguments on attributes into named arguments, using the attribute constructor parameter names', [new CodeSample(<<<'CODE_SAMPLE'
#[SomeAttribute(SomeClass::class, null, ['home'])]
class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[SomeAttribute(value: SomeClass::class, only: null, except: ['home'])]
class SomeClass
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Attribute::class];
    }
    /**
     * @param Attribute $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodReflection = $this->reflectionResolver->resolveConstructorReflectionFromAttribute($node);
        if (!$methodReflection instanceof MethodReflection) {
            return null;
        }
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants());
        $parameters = $extendedParametersAcceptor->getParameters();
        $namesToApply = $this->resolveArgNamesToApply($node->args, $parameters);
        if ($namesToApply === []) {
            return null;
        }
        foreach ($namesToApply as $position => $name) {
            $node->args[$position]->name = new Identifier($name);
        }
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::NAMED_ARGUMENTS;
    }
    /**
     * Resolve the positional arguments to name, as a position => parameter-name map, or [] when
     * nothing should change. Naming an argument forces every later positional argument to be named
     * too (PHP forbids a positional argument after a named one). So if any argument maps to a
     * variadic parameter, or to no parameter at all (overflow past a variadic), the whole attribute
     * is left untouched rather than producing invalid PHP.
     *
     * @param Arg[]                 $args
     * @param ParameterReflection[] $parameters
     * @return array<int, string>
     */
    private function resolveArgNamesToApply(array $args, array $parameters): array
    {
        $namesToApply = [];
        foreach ($args as $position => $arg) {
            // already named
            if ($arg->name instanceof Identifier) {
                continue;
            }
            $parameter = $parameters[$position] ?? null;
            // no matching parameter, e.g. overflow past a variadic
            if ($parameter === null) {
                return [];
            }
            // naming a variadic would rebind it or strand later positional arguments
            if ($parameter->isVariadic()) {
                return [];
            }
            $namesToApply[$position] = $parameter->getName();
        }
        return $namesToApply;
    }
}
