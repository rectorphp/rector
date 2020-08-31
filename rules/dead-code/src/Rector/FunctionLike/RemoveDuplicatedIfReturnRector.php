<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use Rector\Core\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DeadCode\NodeCollector\ModifiedVariableNamesCollector;

/**
 * @see https://github.com/rectorphp/rector/issues/2945
 *
 * @see \Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDuplicatedIfReturnRector\RemoveDuplicatedIfReturnRectorTest
 */
final class RemoveDuplicatedIfReturnRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var ModifiedVariableNamesCollector
     */
    private $modifiedVariableNamesCollector;

    public function __construct(
        IfManipulator $ifManipulator,
        ModifiedVariableNamesCollector $modifiedVariableNamesCollector
    ) {
        $this->ifManipulator = $ifManipulator;
        $this->modifiedVariableNamesCollector = $modifiedVariableNamesCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove duplicated if stmt with return in function/method body', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;

        if ($value) {
            return true;
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            return true;
        }

        $value2 = 100;
    }
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
        return [FunctionLike::class];
    }

    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node): ?Node
    {
        $ifWithOnlyReturnsByHash = $this->collectDuplicatedIfWithOnlyReturnByHash($node);
        if ($ifWithOnlyReturnsByHash === []) {
            return null;
        }

        foreach ($ifWithOnlyReturnsByHash as $stmts) {
            // keep first one
            array_shift($stmts);

            foreach ($stmts as $stmt) {
                $this->removeNode($stmt);
            }
        }

        return $node;
    }

    /**
     * @return If_[][]
     */
    private function collectDuplicatedIfWithOnlyReturnByHash(FunctionLike $functionLike): array
    {
        $ifWithOnlyReturnsByHash = [];
        $modifiedVariableNames = [];

        foreach ((array) $functionLike->getStmts() as $stmt) {
            if (! $this->ifManipulator->isIfWithOnlyReturn($stmt)) {
                // variable modification
                $modifiedVariableNames = array_merge(
                    $modifiedVariableNames,
                    $this->modifiedVariableNamesCollector->collectModifiedVariableNames($stmt)
                );
                continue;
            }

            if ($this->containsVariableNames($stmt, $modifiedVariableNames)) {
                continue;
            }

            /** @var If_ $stmt */
            $hash = $this->printWithoutComments($stmt);
            $ifWithOnlyReturnsByHash[$hash][] = $stmt;
        }

        return $this->filterOutSingleItemStmts($ifWithOnlyReturnsByHash);
    }

    /**
     * @param string[] $modifiedVariableNames
     */
    private function containsVariableNames(Node $node, array $modifiedVariableNames): bool
    {
        if ($modifiedVariableNames === []) {
            return false;
        }

        $containsVariableNames = false;
        $this->traverseNodesWithCallable($node, function (Node $node) use (
            $modifiedVariableNames,
            &$containsVariableNames
        ): ?int {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->isNames($node, $modifiedVariableNames)) {
                return null;
            }

            $containsVariableNames = true;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $containsVariableNames;
    }

    /**
     * @param If_[][] $ifWithOnlyReturnsByHash
     * @return \PhpParser\Node\Stmt\If_[]&mixed[][]
     */
    private function filterOutSingleItemStmts(array $ifWithOnlyReturnsByHash): array
    {
        return array_filter($ifWithOnlyReturnsByHash, function (array $stmts): bool {
            return count($stmts) >= 2;
        });
    }
}
