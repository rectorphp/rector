<?php

declare (strict_types=1);
namespace Rector\Php84\Rector\MethodCall;

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
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
/**
 * @see \Rector\Tests\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector\NewMethodCallWithoutParenthesesRectorTest
 */
final class NewMethodCallWithoutParenthesesRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Remove parentheses around new with arguments on method call, property fetch, static access, array access or invocation', [new CodeSample(<<<'CODE_SAMPLE'
(new Request())->withMethod('GET')->withUri('/hello-world');
$message = (new Regex('some-pattern'))->message;
$value = (new ArrayObject($items))['key'];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
new Request()->withMethod('GET')->withUri('/hello-world');
$message = new Regex('some-pattern')->message;
$value = new ArrayObject($items)['key'];
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
        $loop = 1;
        while (isset($oldTokens[$new->getStartTokenPos() + $loop])) {
            if (trim((string) $oldTokens[$new->getStartTokenPos() + $loop]) === '') {
                ++$loop;
                continue;
            }
            if ((string) $oldTokens[$new->getStartTokenPos() + $loop] !== '(') {
                break;
            }
            return null;
        }
        // start node
        if (!isset($oldTokens[$node->getStartTokenPos()])) {
            return null;
        }
        // end of "new" node
        if (!isset($oldTokens[$new->getEndTokenPos()])) {
            return null;
        }
        if ((string) $oldTokens[$node->getStartTokenPos()] === '(' && (string) $oldTokens[$new->getEndTokenPos()] === ')') {
            $oldTokens[$node->getStartTokenPos()]->text = '';
            $oldTokens[$new->getEndTokenPos()]->text = '';
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_84;
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
