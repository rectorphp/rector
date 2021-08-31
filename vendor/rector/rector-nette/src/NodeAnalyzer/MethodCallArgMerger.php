<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Exception\NotImplementedYetException;
final class MethodCallArgMerger
{
    public function mergeOrApendArray(\PhpParser\Node\Expr\MethodCall $methodCall, int $argumentPosition, \PhpParser\Node\Expr\Array_ $array) : void
    {
        if (!isset($methodCall->args[$argumentPosition])) {
            $methodCall->args[$argumentPosition] = new \PhpParser\Node\Arg($array);
            return;
        }
        $existingParameterArgValue = $methodCall->args[$argumentPosition]->value;
        if (!$existingParameterArgValue instanceof \PhpParser\Node\Expr\Array_) {
            // another parameters than array are not suported yet
            throw new \Rector\Core\Exception\NotImplementedYetException();
        }
        $existingParameterArgValue->items = \array_merge($existingParameterArgValue->items, $array->items);
    }
}
