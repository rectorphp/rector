<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony73\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\TwigClass;
use Rector\Symfony\Symfony73\GetMethodToAsTwigAttributeTransformer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://symfony.com/blog/new-in-symfony-7-3-twig-extension-attributes
 *
 * @see \Rector\Symfony\Tests\Symfony73\Rector\Class_\GetFiltersToAsTwigFilterAttributeRector\GetFiltersToAsTwigFilterAttributeRectorTest
 */
final class GetFiltersToAsTwigFilterAttributeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private GetMethodToAsTwigAttributeTransformer $getMethodToAsTwigAttributeTransformer;
    public function __construct(GetMethodToAsTwigAttributeTransformer $getMethodToAsTwigAttributeTransformer)
    {
        $this->getMethodToAsTwigAttributeTransformer = $getMethodToAsTwigAttributeTransformer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes getFilters() in TwigExtension to #[TwigFilter] marker attribute above function', [new CodeSample(<<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;

class SomeClass extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('filter_name', [$this, 'localMethod']),
        ];
    }

    public function localMethod($value)
    {
        return $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;
use Twig\Attribute\AsTwigFilter;

class SomeClass extends AbstractExtension
{
    #[TwigFilter('filter_name')]
    public function localMethod($value)
    {
        return $value;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if ($node->isAbstract() || $node->isAnonymous()) {
            return null;
        }
        $twigExtensionObjectType = new ObjectType(TwigClass::TWIG_EXTENSION);
        if (!$this->isObjectType($node, $twigExtensionObjectType)) {
            return null;
        }
        $hasChanged = $this->getMethodToAsTwigAttributeTransformer->transformClassGetMethodToAttributeMarker($node, 'getFilters', TwigClass::AS_TWIG_FILTER_ATTRIBUTE, $twigExtensionObjectType);
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
