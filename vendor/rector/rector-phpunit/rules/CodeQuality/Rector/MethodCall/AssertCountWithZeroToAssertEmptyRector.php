<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated and will be removed in future releases. The use of empty() is discouraged as it introduces ambiguity. PHPStan and Rector promote refactoring away from empty() to more explicit readable structures.
 */
final class AssertCountWithZeroToAssertEmptyRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $this->assertCount(0, ...) to $this->assertEmpty(...)', [new CodeSample(<<<'CODE_SAMPLE'
$this->assertCount(0, $countable);
$this->assertNotCount(0, $countable);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->assertEmpty($countable);
$this->assertNotEmpty($countable);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<MethodCall|StaticCall>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    public function refactor(Node $node)
    {
        // deprecated
        return null;
    }
}
