<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/new_without_parentheses
 *
 * @see \Rector\Tests\DowngradePhp84\Rector\MethodCall\DowngradeNewMethodCallWithoutParenthesesRector\DowngradeNewMethodCallWithoutParenthesesRectorTest
 */
final class DowngradeNewMethodCallWithoutParenthesesRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add parentheses on new method call without parentheses', [new CodeSample(<<<'CODE_SAMPLE'
new Request()->withMethod('GET')->withUri('/hello-world');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
(new Request())->withMethod('GET')->withUri('/hello-world');
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->var instanceof New_) {
            return null;
        }
        $oldTokens = $this->getFile()->getOldTokens();
        $startTokenPos = $node->getStartTokenPos();
        $endTokenPos = $node->getEndTokenPos();
        if (!isset($oldTokens[$startTokenPos], $oldTokens[$endTokenPos])) {
            return null;
        }
        // source already wraps the new in parentheses
        if ((string) $oldTokens[$startTokenPos] === '(') {
            return null;
        }
        // idempotency: on a previous pass the opening paren was prepended to the New_ var token,
        // so check that token too, otherwise the rule keeps wrapping the same call every pass
        if (strncmp((string) $oldTokens[$node->var->getStartTokenPos()], '(', strlen('(')) === 0) {
            return null;
        }
        $oldTokens[$node->var->getStartTokenPos()]->text = '(' . $oldTokens[$node->var->getStartTokenPos()];
        $oldTokens[$node->var->getEndTokenPos()]->text .= ')';
        return $node;
    }
}
