<?php

declare (strict_types=1);
namespace Rector\Assert\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as turning a docblock type into a runtime assert is risky and academic. It adds runtime cost to every call and trusts a docblock that is often wrong. Write a custom rule if the project needs it.
 */
final class AddAssertArrayFromClassMethodDocblockRector extends AbstractRector implements ConfigurableRectorInterface, DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add key and value assert based on docblock @param type declarations (pick from "webmozart" or "beberlei" asserts)', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
<?php

class SomeClass
{
    /**
     * @param int[] $items
     */
    public function run(array $items)
    {
    }
}

CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

use Webmozart\Assert\Assert;
class SomeClass
{
    /**
     * @param int[] $items
     */
    public function run(array $items)
    {
        Assert::allInteger($items);
    }
}
CODE_SAMPLE
, ['Webmozart\Assert\Assert'])]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as turning a docblock type into a runtime assert is risky and academic. Write a custom rule if the project needs it', self::class));
    }
    /**
     * @param array<string> $configuration
     */
    public function configure(array $configuration): void
    {
    }
}
