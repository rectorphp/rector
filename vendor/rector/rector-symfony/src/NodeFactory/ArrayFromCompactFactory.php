<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeFactory;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeManipulator\FuncCallManipulator;
final class ArrayFromCompactFactory
{
    /**
     * @var \Rector\Core\NodeManipulator\FuncCallManipulator
     */
    private $funcCallManipulator;
    public function __construct(\Rector\Core\NodeManipulator\FuncCallManipulator $funcCallManipulator)
    {
        $this->funcCallManipulator = $funcCallManipulator;
    }
    public function createArrayFromCompactFuncCall(\PhpParser\Node\Expr\FuncCall $compactFuncCall) : \PhpParser\Node\Expr\Array_
    {
        $compactVariableNames = $this->funcCallManipulator->extractArgumentsFromCompactFuncCalls([$compactFuncCall]);
        $array = new \PhpParser\Node\Expr\Array_();
        foreach ($compactVariableNames as $compactVariableName) {
            $arrayItem = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Variable($compactVariableName), new \PhpParser\Node\Scalar\String_($compactVariableName));
            $array->items[] = $arrayItem;
        }
        return $array;
    }
}
