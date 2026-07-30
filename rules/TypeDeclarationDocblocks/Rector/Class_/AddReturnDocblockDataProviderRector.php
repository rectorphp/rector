<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as data provider docblock typing is not relevant to code quality. It increases maintenance cost and decreases readability.
 */
final class AddReturnDocblockDataProviderRector extends AbstractRector implements DeprecatedInterface
{
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return array docblock to array provider method', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideItems()
     */
    public function testSomething(array $items)
    {
    }

    public function provideItems()
    {
        return [
            [['item1', 'item2']],
            [['item3', 'item4']],
        ];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @dataProvider provideItems()
     */
    public function testSomething(array $items)
    {
    }

    /**
     * @return array<array<string>>
     */
    public function provideItems()
    {
        return [
            [['item1', 'item2']],
            [['item3', 'item4']],
        ];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as data provider docblock typing is not relevant to code quality', self::class));
    }
}
