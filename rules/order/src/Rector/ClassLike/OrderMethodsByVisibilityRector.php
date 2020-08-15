<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Order\StmtVisibilitySorter;

/**
 * @see \Rector\Order\Tests\Rector\ClassLike\OrderMethodsByVisibilityRector\OrderMethodsByVisibilityRectorTest
 */
final class OrderMethodsByVisibilityRector extends AbstractRector
{
    private const PREFERRED_ORDER = [
        '__construct',
        '__destruct',
        '__call',
        '__callStatic',
        '__get',
        '__set',
        '__isset',
        '__unset',
        '__sleep',
        '__wakeup',
        '__serialize',
        '__unserialize',
        '__toString',
        '__invoke',
        '__set_state',
        '__clone',
        'setUpBeforeClass',
        'tearDownAfterClass',
        'setUp',
        'tearDown',
    ];

    /**
     * @var StmtVisibilitySorter
     */
    private $stmtVisibilitySorter;

    public function __construct(StmtVisibilitySorter $stmtVisibilitySorter)
    {
        $this->stmtVisibilitySorter = $stmtVisibilitySorter;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Orders method by visibility', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    protected function protectedFunctionName();
    private function privateFunctionName();
    public function publicFunctionName();
}
PHP

                ,
                <<<'PHP'
class SomeClass
{
    public function publicFunctionName();
    protected function protectedFunctionName();
    private function privateFunctionName();
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

        $node->stmts = $this->stmtVisibilitySorter->sortMethods($node->stmts);
        $node->stmts = $this->applyPreferredOrder($node->stmts);

        return $node;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function applyPreferredOrder(array $stmts): array
    {
        $stmtsByName = [];
        foreach ($stmts as $stmt) {
            $stmtsByName[$this->getName($stmt)] = $stmt;
        }

        $usedPreferredMethods = array_intersect_key(array_flip(self::PREFERRED_ORDER), $stmtsByName);
        return array_merge($usedPreferredMethods, $stmtsByName);
    }
}
