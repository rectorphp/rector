<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.4/array_find-array_find_key-array_any-array_all
 *
 * @see \Rector\Tests\DowngradePhp84\Rector\Expression\DowngradeArrayAllRector\DowngradeArrayAllRectorTest
 */
final class DowngradeArrayAllRector extends AbstractRector
{
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade array_all() to foreach loop', [new CodeSample(<<<'CODE_SAMPLE'
$found = array_all($animals, fn($animal) => str_starts_with($animal, 'c'));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$found = true;
foreach ($animals as $animal) {
    if (!str_starts_with($animal, 'c')) {
        $found = false;
        break;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Expression $node
     * @return Stmt[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        if (!$node->expr->expr instanceof FuncCall) {
            return null;
        }
        if (!$this->isName($node->expr->expr, 'array_all')) {
            return null;
        }
        if ($node->expr->expr->isFirstClassCallable()) {
            return null;
        }
        $args = $node->expr->expr->getArgs();
        if (\count($args) !== 2) {
            return null;
        }
        if (!$args[1]->value instanceof ArrowFunction) {
            return null;
        }
        $valueCond = $args[1]->value->expr;
        $if = new If_(new BooleanNot($valueCond), ['stmts' => [new Expression(new Assign($node->expr->var, new ConstFetch(new Name('false')))), new Break_()]]);
        return [
            // init
            new Expression(new Assign($node->expr->var, new ConstFetch(new Name('true')))),
            // foreach loop
            new Foreach_($args[0]->value, $args[1]->value->params[0]->var, isset($args[1]->value->params[1]->var) ? ['keyVar' => $args[1]->value->params[1]->var, 'stmts' => [$if]] : ['stmts' => [$if]]),
        ];
    }
}
