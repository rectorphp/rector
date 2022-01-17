<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\ValueObject\MethodName;
use Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory;
final class SetUpClassMethodNodeManipulator
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory
     */
    private $setUpClassMethodFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeManipulator\StmtManipulator
     */
    private $stmtManipulator;
    public function __construct(\Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory $setUpClassMethodFactory, \Rector\PHPUnit\NodeManipulator\StmtManipulator $stmtManipulator)
    {
        $this->setUpClassMethodFactory = $setUpClassMethodFactory;
        $this->stmtManipulator = $stmtManipulator;
    }
    /**
     * @param Stmt[]|Expr[] $stmts
     */
    public function decorateOrCreate(\PhpParser\Node\Stmt\Class_ $class, array $stmts) : void
    {
        $stmts = $this->stmtManipulator->normalizeStmts($stmts);
        $setUpClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::SET_UP);
        if ($setUpClassMethod === null) {
            $setUpClassMethod = $this->setUpClassMethodFactory->createSetUpMethod($stmts);
            $class->stmts = \array_merge([$setUpClassMethod], $class->stmts);
        } else {
            $setUpClassMethod->stmts = \array_merge((array) $setUpClassMethod->stmts, $stmts);
        }
    }
}
