<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Attribute;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PHPStan\Reflection\MethodReflection;
use Rector\CodeQuality\NodeManipulator\NamedArgsSorter;
use Rector\NodeAnalyzer\ArgsAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Attribute\SortAttributeNamedArgsRector\SortAttributeNamedArgsRectorTest
 */
final class SortAttributeNamedArgsRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ArgsAnalyzer $argsAnalyzer;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private NamedArgsSorter $namedArgsSorter;
    public function __construct(ArgsAnalyzer $argsAnalyzer, ReflectionResolver $reflectionResolver, NamedArgsSorter $namedArgsSorter)
    {
        $this->argsAnalyzer = $argsAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->namedArgsSorter = $namedArgsSorter;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Sort named arguments in PHP 8 attributes to match their declaration order', [new CodeSample(<<<'CODE_SAMPLE'
#[SomeAttribute(bar: $bar, foo: $foo)]
class SomeClass
{
}

#[Attribute]
class SomeAttribute
{
    public function __construct(public $foo, public $bar)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
#[SomeAttribute(foo: $foo, bar: $bar)]
class SomeClass
{
}

#[Attribute]
class SomeAttribute
{
    public function __construct(public $foo, public $bar)
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Attribute::class];
    }
    /**
     * @param Node\Attribute $node
     */
    public function refactor(Node $node): ?Node
    {
        $args = $node->args;
        if (count($args) <= 1) {
            return null;
        }
        if (!$this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        $functionLikeReflection = $this->reflectionResolver->resolveConstructorReflectionFromAttribute($node);
        if (!$functionLikeReflection instanceof MethodReflection) {
            return null;
        }
        $args = $this->namedArgsSorter->sortArgsToMatchReflectionParameters($args, $functionLikeReflection);
        if ($node->args === $args) {
            return null;
        }
        $node->args = $args;
        return $node;
    }
}
