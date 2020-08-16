<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Order\Tests\Rector\ClassLike\OrderFirstLevelClassStatementsRector\OrderFirstLevelClassStatementsRectorTest
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
        return [ClassLike::class];
    }

    /**
     * @param ClassLike $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Interface_) {
            return null;
        }

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
                return [
                    $this->getOrder($firstStmt),
                    $firstStmt->getLine(),
                ] <=> [$this->getOrder($secondStmt), $secondStmt->getLine()];
            }
        );

        return $stmts;
    }

    private function getOrder(Stmt $stmt): int
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
