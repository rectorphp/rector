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
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Order\Tests\Rector\Class_\OrderFirstLevelClassStatementsRector\OrderFirstLevelClassStatementsRectorTest
 */
final class OrderFirstLevelClassStatementsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Orders first level Class statements', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function functionName();
    protected $propertyName;
    private const CONST_NAME = 'constant_value';
    use TraitName;
}
PHP

                ,
                <<<'PHP'
final class SomeClass
{
    use TraitName;
    private const CONST_NAME = 'constant_value';
    protected $propertyName;
    public function functionName();
}
PHP

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
        if ($stmt instanceof ClassMethod) {
            return 3;
        }

        if ($stmt instanceof Property) {
            return 2;
        }

        if ($stmt instanceof ClassConst) {
            return 1;
        }

        // TraitUse
        return 0;
    }
}
