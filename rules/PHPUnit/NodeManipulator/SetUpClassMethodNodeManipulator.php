<?php
declare(strict_types=1);

namespace Rector\PHPUnit\NodeManipulator;

use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\ValueObject\MethodName;
use Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory;

final class SetUpClassMethodNodeManipulator
{
    /**
     * @var SetUpClassMethodFactory
     */
    private $setUpClassMethodFactory;

    /**
     * @var StmtManipulator
     */
    private $stmtManipulator;

    public function __construct(
        SetUpClassMethodFactory $setUpClassMethodFactory,
        StmtManipulator $stmtManipulator
    ) {
        $this->setUpClassMethodFactory = $setUpClassMethodFactory;
        $this->stmtManipulator = $stmtManipulator;
    }

    /**
     * @param Stmt[]|Expr[] $stmts
     */
    public function decorateOrCreate(Class_ $class, array $stmts): void
    {
        $stmts = $this->stmtManipulator->normalizeStmts($stmts);

        $setUpClassMethod = $class->getMethod(MethodName::SET_UP);

        if ($setUpClassMethod === null) {
            $setUpClassMethod = $this->setUpClassMethodFactory->createSetUpMethod($stmts);
            $class->stmts = array_merge([$setUpClassMethod], $class->stmts);
        } else {
            $setUpClassMethod->stmts = array_merge((array) $setUpClassMethod->stmts, $stmts);
        }
    }
}
