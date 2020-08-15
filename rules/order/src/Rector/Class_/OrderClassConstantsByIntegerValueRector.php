<?php

declare(strict_types=1);

namespace Rector\Order\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Order\Tests\Rector\Class_\OrderClassConstantsByIntegerValueRector\OrderClassConstantsByIntegerValueRectorTest
 */
final class OrderClassConstantsByIntegerValueRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Order class constant order by their integer value', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    const MODE_ON = 0;

    const MODE_OFF = 2;

    const MODE_MAYBE = 1;
}
PHP
,
                <<<'PHP'
class SomeClass
{
    const MODE_ON = 0;

    const MODE_MAYBE = 1;

    const MODE_OFF = 2;
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->stmts = $this->sortConstantsByValue($node->stmts);
        return $node;
    }

    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    private function sortConstantsByValue(array $stmts): array
    {
        usort(
            $stmts,
            function (Stmt $firstStmt, Stmt $secondStmt): int {
                if (! $firstStmt instanceof ClassConst || ! $secondStmt instanceof ClassConst) {
                    return $firstStmt->getLine() <=> $secondStmt->getLine();
                }

                return [
                    $this->getValue($firstStmt->consts[0]->value),
                    $firstStmt->getLine(),
                ] <=> [$this->getValue($secondStmt->consts[0]->value), $secondStmt->getLine()];
            }
        );
        return $stmts;
    }
}
