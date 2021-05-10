<?php

declare(strict_types=1);

namespace Rector\CodingStyle\NodeFactory;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Rector\Core\PhpParser\Node\NodeFactory;

/**
 * Creates + adds
 *
 * $jsonData = ['...'];
 * $json = Nette\Utils\Json::encode($jsonData);
 */
final class JsonEncodeStaticCallFactory
{
    public function __construct(
        private NodeFactory $nodeFactory
    ) {
    }

    /**
     * Creates + adds
     *
     * $jsonData = ['...'];
     * $json = Nette\Utils\Json::encode($jsonData);
     */
    public function createFromArray(Expr $assignExpr, Array_ $jsonArray): Assign
    {
        $jsonDataAssign = new Assign($assignExpr, $jsonArray);

        $jsonDataVariable = new Variable('jsonData');
        $jsonDataAssign->expr = $this->nodeFactory->createStaticCall('Nette\Utils\Json', 'encode', [$jsonDataVariable]);

        return $jsonDataAssign;
    }
}
