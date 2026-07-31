<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated as it is not a common upgrade path and it is not part of any set. Implement it as a custom rule instead.
 */
final class ReplaceTestFunctionPrefixWithAttributeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace @test with prefixed function', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function testOnePlusOneShouldBeTwo()
    {
        $this->assertSame(2, 1+1);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function onePlusOneShouldBeTwo()
    {
        $this->assertSame(2, 1+1);
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated and should not be used anymore. Remove it from your config files.', self::class));
    }
}
