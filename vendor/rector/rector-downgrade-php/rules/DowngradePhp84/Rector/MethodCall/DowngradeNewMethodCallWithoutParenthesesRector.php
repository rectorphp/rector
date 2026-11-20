<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
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
        return [MethodCall::class, NullsafeMethodCall::class, PropertyFetch::class, NullsafePropertyFetch::class, StaticCall::class, StaticPropertyFetch::class, ClassConstFetch::class, ArrayDimFetch::class, FuncCall::class];
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add parentheses around new on method call, property fetch, static access, array access or invocation', [new CodeSample(<<<'CODE_SAMPLE'
new Request()->withMethod('GET')->withUri('/hello-world');
$message = new Regex('some-pattern')->message;
$value = new ArrayObject($items)['key'];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
(new Request())->withMethod('GET')->withUri('/hello-world');
$message = (new Regex('some-pattern'))->message;
$value = (new ArrayObject($items))['key'];
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall|NullsafeMethodCall|PropertyFetch|NullsafePropertyFetch|StaticCall|StaticPropertyFetch|ClassConstFetch|ArrayDimFetch|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $new = $this->resolveDereferencedExpr($node);
        if (!$new instanceof New_) {
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
        if (strncmp((string) $oldTokens[$new->getStartTokenPos()], '(', strlen('(')) === 0) {
            return null;
        }
        $oldTokens[$new->getStartTokenPos()]->text = '(' . $oldTokens[$new->getStartTokenPos()];
        $oldTokens[$new->getEndTokenPos()]->text .= ')';
        return $node;
    }
    /**
     * @param \PhpParser\Node\Expr\ArrayDimFetch|\PhpParser\Node\Expr\ClassConstFetch|\PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\NullsafeMethodCall|\PhpParser\Node\Expr\NullsafePropertyFetch|\PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\StaticPropertyFetch $node
     */
    private function resolveDereferencedExpr($node): ?Node
    {
        if ($node instanceof StaticCall || $node instanceof StaticPropertyFetch || $node instanceof ClassConstFetch) {
            return $node->class;
        }
        if ($node instanceof FuncCall) {
            return $node->name instanceof Expr ? $node->name : null;
        }
        return $node->var;
    }
}
