<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\TwigClass;
use Rector\Symfony\Symfony73\GetMethodsToAsTwigAttributeTransformer;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-7-3-twig-extension-attributes
 *
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\GetFiltersAndFunctionsToAsTwigAttributeRector\GetFiltersAndFunctionsToAsTwigAttributeRectorTest
 */
final class GetFiltersAndFunctionsToAsTwigAttributeRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private GetMethodsToAsTwigAttributeTransformer $getMethodsToAsTwigAttributeTransformer;
    public function __construct(GetMethodsToAsTwigAttributeTransformer $getMethodsToAsTwigAttributeTransformer)
    {
        $this->getMethodsToAsTwigAttributeTransformer = $getMethodsToAsTwigAttributeTransformer;
    }
    /**
     * @return ComposerPackageConstraint[]
     */
    public function provideComposerPackageConstraint(): array
    {
        // the #[AsTwig*] attributes exist in twig/twig 3.21, but Symfony only autoregisters extension-less
        // classes from them in symfony/twig-bridge 7.3; without both, the stripped TwigExtension is silently lost
        return [new ComposerPackageConstraint('twig/twig', '>=3.21'), new ComposerPackageConstraint('symfony/twig-bridge', '>=7.3')];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes getFilters(), getFunctions() and getTests() in TwigExtension to #[AsTwigFilter], #[AsTwigFunction] and #[AsTwigTest] marker attributes above local class methods', [new CodeSample(<<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;
use Twig\Environment;

class SomeClass extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('filter_name', [$this, 'localMethod'], ['needs_environment' => true]),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('function_name', [$this, 'localMethod'], ['needs_environment' => true]),
        ];
    }

    public function localMethod(Environment $env, $value)
    {
        return $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Twig\Attribute\AsTwigFilter;
use Twig\Attribute\AsTwigFunction;
use Twig\Environment;

class SomeClass
{
    #[AsTwigFilter(name: 'filter_name', needsEnvironment: true)]
    #[AsTwigFunction(name: 'function_name', needsEnvironment: true)]
    public function localMethod(Environment $env, $value)
    {
        return $value;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Class_
    {
        if ($node->isAbstract() || $node->isAnonymous()) {
            return null;
        }
        $twigExtensionObjectType = new ObjectType(TwigClass::TWIG_EXTENSION);
        if (!$this->isObjectType($node, $twigExtensionObjectType)) {
            return null;
        }
        $hasChanged = $this->getMethodsToAsTwigAttributeTransformer->transformClassGetMethodsToAttributeMarkers($node, $twigExtensionObjectType);
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
