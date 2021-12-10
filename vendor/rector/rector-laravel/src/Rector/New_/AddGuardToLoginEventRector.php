<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/laravel/framework/commit/f5d8c0a673aa9fc6cd94aa4858a0027fe550a22e#diff-162a49c054acde9f386ec735607b95bc4a1c0c765a6f46da8de9a8a4ef5199d3
 * @see https://github.com/laravel/framework/pull/25261
 *
 * @see \Rector\Laravel\Tests\Rector\New_\AddGuardToLoginEventRector\AddGuardToLoginEventRectorTest
 */
final class AddGuardToLoginEventRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add new $guard argument to Illuminate\\Auth\\Events\\Login', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Illuminate\Auth\Events\Login;

final class SomeClass
{
    public function run(): void
    {
        $loginEvent = new Login('user', false);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Illuminate\Auth\Events\Login;

final class SomeClass
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard');
        $loginEvent = new Login($guard, 'user', false);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isName($node->class, 'Illuminate\\Auth\\Events\\Login')) {
            return null;
        }
        if (\count($node->args) === 3) {
            return null;
        }
        $guardVariable = new \PhpParser\Node\Expr\Variable('guard');
        $assign = $this->createGuardAssign($guardVariable);
        $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);
        $node->args = \array_merge([new \PhpParser\Node\Arg($guardVariable)], $node->args);
        return $node;
    }
    private function createGuardAssign(\PhpParser\Node\Expr\Variable $guardVariable) : \PhpParser\Node\Expr\Assign
    {
        $string = new \PhpParser\Node\Scalar\String_('auth.defaults.guard');
        return new \PhpParser\Node\Expr\Assign($guardVariable, $this->nodeFactory->createFuncCall('config', [$string]));
    }
}
