<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\NullsafeMethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector\DowngradeNullsafeToTernaryOperatorRectorTest
 */
final class DowngradeNullsafeToTernaryOperatorRector extends AbstractRector
{
    private int $counter = 0;
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change nullsafe operator to ternary operator rector', [new CodeSample(<<<'CODE_SAMPLE'
$dateAsString = $booking->getStartDate()?->asDateTimeString();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$dateAsString = ($bookingGetStartDate = $booking->getStartDate()) ? $bookingGetStartDate->asDateTimeString() : null;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, PropertyFetch::class, NullsafeMethodCall::class, NullsafePropertyFetch::class];
    }
    /**
     * @param MethodCall|NullsafeMethodCall|NullsafePropertyFetch $node
     */
    public function refactor(Node $node): ?Ternary
    {
        static $currentFile = null;
        if ($currentFile !== $this->file->getFilePath()) {
            // the counter need start from 0 when visit new file to avoid random increment
            // across files
            // due to run on parallel
            $this->counter = 0;
            $currentFile = $this->file->getFilePath();
        }
        if ($node instanceof MethodCall || $node instanceof PropertyFetch) {
            if ($node->var instanceof NullsafeMethodCall || $node->var instanceof NullsafePropertyFetch) {
                $nullsafeVariable = $this->createNullsafeVariable();
                $assign = new Assign($nullsafeVariable, $node->var->var);
                if ($node instanceof MethodCall) {
                    if ($node->var instanceof NullsafeMethodCall) {
                        $methodCallOrPropertyFetch = new MethodCall(new MethodCall($nullsafeVariable, $node->var->name, $node->var->args), $node->name, $node->args);
                        return new Ternary($assign, $methodCallOrPropertyFetch, $this->nodeFactory->createNull());
                    }
                    $methodCallOrPropertyFetch = new MethodCall(new PropertyFetch($nullsafeVariable, $node->var->name), $node->name, $node->args);
                    return new Ternary($assign, $methodCallOrPropertyFetch, $this->nodeFactory->createNull());
                }
                if ($node->var instanceof NullsafeMethodCall) {
                    $methodCallOrPropertyFetch = new PropertyFetch(new MethodCall($nullsafeVariable, $node->var->name, $node->var->args), $node->name);
                    return new Ternary($assign, $methodCallOrPropertyFetch, $this->nodeFactory->createNull());
                }
                $methodCallOrPropertyFetch = new PropertyFetch(new PropertyFetch($nullsafeVariable, $node->var->name), $node->name);
                return new Ternary($assign, $methodCallOrPropertyFetch, $this->nodeFactory->createNull());
            }
            return null;
        }
        $nullsafeVariable = $this->createNullsafeVariable();
        $methodCallOrPropertyFetch = $node instanceof NullsafeMethodCall ? new MethodCall($nullsafeVariable, $node->name, $node->args) : new PropertyFetch($nullsafeVariable, $node->name);
        $assign = new Assign($nullsafeVariable, $node->var);
        return new Ternary($assign, $methodCallOrPropertyFetch, $this->nodeFactory->createNull());
    }
    private function createNullsafeVariable(): Variable
    {
        $nullsafeVariableName = 'nullsafeVariable' . ++$this->counter;
        return new Variable($nullsafeVariableName);
    }
}
