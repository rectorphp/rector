<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\ClassMethod;

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
final class AddParamArrayDocblockFromDataProviderRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add @param docblock array type, based on data provider data type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SomeTest extends TestCase
{
    #[DataProvider('provideData')]
    public function test(array $names): void
    {
    }

    public static function provideData()
    {
        yield [['Tom', 'John']];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SomeTest extends TestCase
{
    /**
     * @param string[] $names
     */
    #[DataProvider('provideData')]
    public function test(array $names): void
    {
    }

    public static function provideData()
    {
        yield [['Tom', 'John']];
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
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated, as data provider docblock typing is not relevant to code quality', self::class));
    }
}
