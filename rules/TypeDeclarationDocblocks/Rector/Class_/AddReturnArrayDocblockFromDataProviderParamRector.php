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
final class AddReturnArrayDocblockFromDataProviderParamRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @return array return from data provider param type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    /**
     * @dataProvider provideNames()
     */
    public function test(string $name)
    {
    }

    public function provideNames(): array
    {
        return ['John', 'Jane'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    /**
     * @dataProvider provideNames()
     */
    public function test(string $name)
    {
    }

    /**
     * @return string[]
     */
    public function provideNames(): array
    {
        return ['John', 'Jane'];
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
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as data provider docblock typing is not relevant to code quality', self::class));
    }
}
