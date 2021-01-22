<?php

declare(strict_types=1);

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
 * @see \Rector\Order\Tests\Rector\Class_\OrderFirstLevelClassStatementsRector\OrderFirstLevelClassStatementsRectorTest
 */
final class OrderFirstLevelClassStatementsRector extends AbstractRector
{
    /**
     * @var array<string, int>
     */
    private const TYPE_TO_RANK = [
        ClassMethod::class => 3,
        Property::class => 2,
        ClassConst::class => 1,
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Orders first level Class statements', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function functionName();
    protected $propertyName;
    private const CONST_NAME = 'constant_value';
    use TraitName;
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    use TraitName;
    private const CONST_NAME = 'constant_value';
    protected $propertyName;
    public function functionName();
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class, Trait_::class];
    }

    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->stmts = $this->getStmtsInDesiredPosition($node->stmts);

        return $node;
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function getStmtsInDesiredPosition(array $stmts): array
    {
        uasort(
            $stmts,
            function (Stmt $firstStmt, Stmt $secondStmt): int {
                return [$this->resolveClassElementRank($firstStmt), $firstStmt->getLine()]
                    <=> [$this->resolveClassElementRank($secondStmt), $secondStmt->getLine()];
            }
        );

        return $stmts;
    }

    private function resolveClassElementRank(Stmt $stmt): int
    {
        foreach (self::TYPE_TO_RANK as $type => $rank) {
            if (is_a($stmt, $type, true)) {
                return $rank;
            }
        }

        // TraitUse
        return 0;
    }
}
