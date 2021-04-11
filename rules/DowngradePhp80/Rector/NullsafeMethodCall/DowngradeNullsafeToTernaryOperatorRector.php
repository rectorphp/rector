<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\NullsafeMethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\DowngradeNullsafeToTernaryOperatorRectorTest
 */
final class DowngradeNullsafeToTernaryOperatorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change nullsafe operator to ternary operator rector', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$dateAsString = $booking->getStartDate()?->asDateTimeString();
$dateAsString = $booking->startDate?->dateTimeString;
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
$dateAsString = ($_ = $booking->getStartDate()) ? $_->asDateTimeString() : null;
$dateAsString = ($_ = $booking->startDate) ? $_->dateTimeString : null;
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [NullsafeMethodCall::class, NullsafePropertyFetch::class];
    }

    /**
     * @param NullsafeMethodCall|NullsafePropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        $temp_var = new Node\Expr\Variable('_');
        $called = $node instanceof NullsafeMethodCall
            ? new MethodCall($temp_var, $node->name, $node->args)
            : new PropertyFetch($temp_var, $node->name);

        return new Ternary(new Assign($temp_var, $node->var), $called, $this->nodeFactory->createNull());
    }
}
