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
 * @deprecated This rule is deprecated as too narrow, it only targets Behat context files. Implement it as a custom rule instead.
 */
final class BehatPHPUnitAssertToWebmozartRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change PHPUnit assert in Behat context files to Webmozart Assert, as first require a TestCase instance', [new CodeSample(<<<'CODE_SAMPLE'
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class SomeContext implements Context
{
    public function someMethod()
    {
        Assert::assertSame('expected', 'actual');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class SomeContext implements Context
{
    public function someMethod()
    {
        Assert::same('actual', 'expected');
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
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated as too narrow. Implement it as a custom rule instead.', self::class));
    }
}
