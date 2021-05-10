<?php

declare (strict_types=1);
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
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * Creates + adds
     *
     * $jsonData = ['...'];
     * $json = Nette\Utils\Json::encode($jsonData);
     */
    public function createFromArray(\PhpParser\Node\Expr $assignExpr, \PhpParser\Node\Expr\Array_ $jsonArray) : \PhpParser\Node\Expr\Assign
    {
        $jsonDataAssign = new \PhpParser\Node\Expr\Assign($assignExpr, $jsonArray);
        $jsonDataVariable = new \PhpParser\Node\Expr\Variable('jsonData');
        $jsonDataAssign->expr = $this->nodeFactory->createStaticCall('Nette\\Utils\\Json', 'encode', [$jsonDataVariable]);
        return $jsonDataAssign;
    }
}
