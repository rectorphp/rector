<?php

declare (strict_types=1);
namespace Rector\Symfony\DependencyInjection\Rector\Trait_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Trait_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated A trait has no context about the class it is used in, so the `$this->get()` call cannot be safely resolved. This rule was made for a single custom project and does not generalize.
 */
final class TraitGetByTypeToInjectRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('From `$this->get(SomeType::class)` in traits, to autowired method with @required', [new CodeSample(<<<'CODE_SAMPLE'
// must be used in old Controller class
trait SomeInjects
{
    public function someMethod()
    {
        return $this->get(SomeType::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
trait SomeInjects
{
    private SomeType $someType;

    /**
     * @required
     */
    public function autowireSomeInjects(SomeType $someType): void
    {
        $this->someType = $someType;
    }

    public function someMethod()
    {
        return $this->someType;
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
        return [Trait_::class];
    }
    /**
     * @param Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as a trait has no context about the class it is used in. Handle the `$this->get()` calls in the using class instead.', self::class));
    }
}
