<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as the keys can be useful to improve test readability
 *
 * @see https://github.com/rectorphp/rector-phpunit/pull/469#issuecomment-3533161836
 */
final class RemoveDataProviderParamKeysRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove data provider keys, that should match param names, as order is good enough', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\DataProvider;

final class SomeServiceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function test(string $name): void
    {
    }

    public function provideData(): array
    {
        return [
            'name' => ['Tom'],
        ];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\DataProvider;

final class SomeServiceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function test(string $name): void
    {
    }

    public function provideData(): array
    {
        return [
            ['Tom'],
        ];
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated and should not be used anymore. Remove it from your config files.', self::class));
    }
}
