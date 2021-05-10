<?php

declare (strict_types=1);
namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Order\Rector\Class_\OrderFirstLevelClassStatementsRector\OrderFirstLevelClassStatementsRectorTest
 */
final class OrderFirstLevelClassStatementsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, int>
     */
    private const TYPE_TO_RANK = [\PhpParser\Node\Stmt\ClassMethod::class => 3, \PhpParser\Node\Stmt\Property::class => 2, \PhpParser\Node\Stmt\ClassConst::class => 1];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Orders first level Class statements', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function functionName();
    protected $propertyName;
    private const CONST_NAME = 'constant_value';
    use TraitName;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    use TraitName;
    private const CONST_NAME = 'constant_value';
    protected $propertyName;
    public function functionName();
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Trait_::class];
    }
    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $node->stmts = $this->getStmtsInDesiredPosition($node->stmts);
        return $node;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function getStmtsInDesiredPosition(array $stmts) : array
    {
        \uasort($stmts, function (\PhpParser\Node\Stmt $firstStmt, \PhpParser\Node\Stmt $secondStmt) : int {
            return [$this->resolveClassElementRank($firstStmt), $firstStmt->getLine()] <=> [$this->resolveClassElementRank($secondStmt), $secondStmt->getLine()];
        });
        return $stmts;
    }
    private function resolveClassElementRank(\PhpParser\Node\Stmt $stmt) : int
    {
        foreach (self::TYPE_TO_RANK as $type => $rank) {
            if (\is_a($stmt, $type, \true)) {
                return $rank;
            }
        }
        // TraitUse
        return 0;
    }
}
