<?php

declare (strict_types=1);
namespace Rector\EarlyReturn\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Rector\NodeNestingScope\ContextAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class InvertedIfFactory
{
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeTransformer\ConditionInverter
     */
    private $conditionInverter;
    /**
     * @readonly
     * @var \Rector\NodeNestingScope\ContextAnalyzer
     */
    private $contextAnalyzer;
    public function __construct(ConditionInverter $conditionInverter, ContextAnalyzer $contextAnalyzer)
    {
        $this->conditionInverter = $conditionInverter;
        $this->contextAnalyzer = $contextAnalyzer;
    }
    /**
     * @param Expr[] $conditions
     * @return If_[]
     */
    public function createFromConditions(If_ $if, array $conditions, Return_ $return, ?Stmt $ifNextReturn) : array
    {
        $ifs = [];
        $stmt = $this->contextAnalyzer->isInLoop($if) && !$ifNextReturn instanceof Return_ ? [new Continue_()] : [$return];
        if ($ifNextReturn instanceof Return_) {
            $stmt[0]->setAttribute(AttributeKey::COMMENTS, $ifNextReturn->getAttribute(AttributeKey::COMMENTS));
        }
        if ($ifNextReturn instanceof Return_ && $ifNextReturn->expr instanceof Expr) {
            $return->expr = $ifNextReturn->expr;
        }
        foreach ($conditions as $condition) {
            $invertedCondition = $this->conditionInverter->createInvertedCondition($condition);
            $if = new If_($invertedCondition);
            $if->stmts = $stmt;
            $ifs[] = $if;
        }
        return $ifs;
    }
}
