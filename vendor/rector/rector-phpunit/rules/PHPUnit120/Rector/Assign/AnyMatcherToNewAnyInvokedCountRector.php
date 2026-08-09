<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * The TestCase::any() method was deprecated in PHPUnit 12.5
 *
 * @see https://github.com/sebastianbergmann/phpunit/issues/6461
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\Assign\AnyMatcherToNewAnyInvokedCountRector\AnyMatcherToNewAnyInvokedCountRectorTest
 */
final class AnyMatcherToNewAnyInvokedCountRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @var string
     */
    private const ANY_INVOKED_COUNT_CLASS = 'PHPUnit\Framework\MockObject\Rule\AnyInvokedCount';
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=12.5');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change deprecated `$this->any()` matcher assign to direct `new AnyInvokedCount()`', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $matcher = $this->any();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\MockObject\Rule\AnyInvokedCount;
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test(): void
    {
        $matcher = new AnyInvokedCount();
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
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Assign
    {
        if (!$node->expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $node->expr;
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($methodCall->name, 'any')) {
            return null;
        }
        if ($methodCall->getArgs() !== []) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isPHPUnitTestCaseCall($methodCall)) {
            return null;
        }
        $node->expr = new New_(new FullyQualified(self::ANY_INVOKED_COUNT_CLASS));
        return $node;
    }
}
