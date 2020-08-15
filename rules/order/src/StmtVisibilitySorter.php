<?php

declare(strict_types=1);

namespace Rector\Order;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;

final class StmtVisibilitySorter
{
    /**
     * @var Property[]
     * @return Property[]
     */
    public function sortProperties(array $stmts): array
    {
        usort(
            $stmts,
            function (Stmt $firstStmt, Stmt $secondStmt): int {
                if (! $firstStmt instanceof Property || ! $secondStmt instanceof Property) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }
                return [
                    $this->getVisibilityLevelOrder($firstStmt),
                    $firstStmt->isStatic(),
                    $firstStmt->getLine(),
                ] <=> [
                    $this->getVisibilityLevelOrder($secondStmt),
                    $secondStmt->isStatic(),
                    $secondStmt->getLine(),
                ];
            }
        );

        return $stmts;
    }

    /**
     * @var ClassMethod[]
     * @return ClassMethod[]
     */
    public function sortMethods(array $stmts): array
    {
        usort(
            $stmts,
            function (Stmt $firstStmt, Stmt $secondStmt): int {
                if (! $firstStmt instanceof ClassMethod || ! $secondStmt instanceof ClassMethod) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }
                return [
                    $this->getVisibilityLevelOrder($firstStmt),
                    $firstStmt->isStatic(),
                    $secondStmt->isAbstract(),
                    $firstStmt->isFinal(),
                    $firstStmt->getLine(),
                ] <=> [
                    $this->getVisibilityLevelOrder($secondStmt),
                    $secondStmt->isStatic(),
                    $firstStmt->isAbstract(),
                    $secondStmt->isFinal(),
                    $secondStmt->getLine(),
                ];
            }
        );

        return $stmts;
    }

    /**
     * @var ClassConst[]
     * @return ClassConst[]
     */
    public function sortConstants(array $stmts): array
    {
        usort(
            $stmts,
            function (Stmt $firstStmt, Stmt $secondStmt): int {
                if (! $firstStmt instanceof ClassConst || ! $secondStmt instanceof ClassConst) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }

                return [
                    $this->getVisibilityLevelOrder($firstStmt),
                    $firstStmt->getLine(),
                ] <=> [$this->getVisibilityLevelOrder($secondStmt), $secondStmt->getLine()];
            }
        );

        return $stmts;
    }

    /**
     * @param ClassMethod|Property|ClassConst $stmt
     */
    private function getVisibilityLevelOrder(Stmt $stmt): int
    {
        if ($stmt->isPrivate()) {
            return 2;
        }

        if ($stmt->isProtected()) {
            return 1;
        }

        return 0;
    }
}
