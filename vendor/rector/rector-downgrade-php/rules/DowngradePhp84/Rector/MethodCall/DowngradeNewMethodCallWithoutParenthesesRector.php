<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
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
    public function refactor(Node $node) : ?Node
    {
        if (!$node->var instanceof New_) {
            return null;
        }
        $oldTokens = $this->file->getOldTokens();
        if (isset($oldTokens[$node->getStartTokenPos()]) && (string) $oldTokens[$node->getStartTokenPos()] === '(') {
            return null;
        }
        $node->var->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
}
