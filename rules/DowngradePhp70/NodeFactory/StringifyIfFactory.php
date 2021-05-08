<?php

declare(strict_types=1);

namespace Rector\DowngradePhp70\NodeFactory;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\NodeFactory;

final class StringifyIfFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }

    public function createObjetVariableStringCast(string $variableName): If_
    {
        $variable = new Variable($variableName);
        $isObjectFuncCall = $this->nodeFactory->createFuncCall('is_object', [$variable]);

        $if = new If_($isObjectFuncCall);
        $assign = new Assign($variable, new String_($variable));
        $if->stmts[] = new Expression($assign);

        return $if;
    }
}
