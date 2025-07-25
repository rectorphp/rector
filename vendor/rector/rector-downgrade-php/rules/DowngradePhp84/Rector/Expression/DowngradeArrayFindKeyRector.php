<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Naming\Naming\VariableNaming;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://php.watch/versions/8.4/array_find-array_find_key-array_any-array_all
 *
 * @see \Rector\Tests\DowngradePhp84\Rector\Expression\DowngradeArrayFindKeyRector\DowngradeArrayFindKeyRectorTest
 */
final class DowngradeArrayFindKeyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VariableNaming $variableNaming;
    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade array_find_key() to foreach loop', [new CodeSample(<<<'CODE_SAMPLE'
$found = array_find_key($animals, fn($animal) => str_starts_with($animal, 'c'));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$found = null;
foreach ($animals as $idx => $animal) {
    if (str_starts_with($animal, 'c')) {
        $found = $idx;
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
        if (!$this->isName($node->expr->expr, 'array_find_key')) {
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
        $keyVar = $args[1]->value->params[1]->var ?? new Variable($this->variableNaming->createCountedValueName('idx', ScopeFetcher::fetch($node)));
        $valueCond = $args[1]->value->expr;
        $if = new If_($valueCond, ['stmts' => [new Expression(new Assign($node->expr->var, $keyVar)), new Break_()]]);
        return [
            // init
            new Expression(new Assign($node->expr->var, new ConstFetch(new Name('null')))),
            // foreach loop
            new Foreach_($args[0]->value, $args[1]->value->params[0]->var, ['keyVar' => $keyVar, 'stmts' => [$if]]),
        ];
    }
}
