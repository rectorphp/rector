<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Handling getFunctions() alone leaves the sibling getFilters()/getTests() methods behind and can produce
 *             a half-converted extension. Use the GetFiltersAndFunctionsToAsTwigAttributeRector rule instead, that
 *             converts all the get methods at once.
 */
final class GetFunctionsToAsTwigFunctionAttributeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes getFunctions() in TwigExtension to #[AsTwigFunction] marker attribute above local class method', [new CodeSample(<<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;
use Twig\Environment;

class SomeClass extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('function_name', [$this, 'localMethod', 'needs_environment' => true]),
        ];
    }

    public function localMethod(Environment $env, $value)
    {
        return $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;
use Twig\Attribute\AsTwigFunction;
use Twig\Environment;

class SomeClass extends AbstractExtension
{
    #[AsTwigFunction(name: 'function_name', needsEnvironment: true)]
    public function localMethod(Environment $env, $value)
    {
        return $value;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as it converts getFunctions() only and leaves getFilters()/getTests() behind. Use "%s" instead.', self::class, \Rector\Symfony\Symfony73\Rector\Class_\GetFiltersAndFunctionsToAsTwigAttributeRector::class));
    }
}
