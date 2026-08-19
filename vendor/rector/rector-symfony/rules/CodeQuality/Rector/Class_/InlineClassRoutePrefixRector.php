<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is opinionated - inlining the class route prefix into every method route removes a single shared source of truth and, on controllers with many routes, can worsen readability rather than improve it.
 */
final class InlineClassRoutePrefixRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Inline class route prefix to all method routes, to make single explicit source for route paths', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class SomeController
{
    /**
     * @Route("/action")
     */
    public function action()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="api_")
 */
class SomeController
{
    /**
     * @Route("/api/action")
     */
    public function action()
    {
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as inlining the class route prefix is opinionated and can worsen readability.', self::class));
    }
}
