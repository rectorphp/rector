<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Nette\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\Rector\Core\Exception\NotImplementedYetException;
final class MethodCallArgMerger
{
    public function mergeOrApendArray(MethodCall $methodCall, int $argumentPosition, Array_ $array) : void
    {
        if (!isset($methodCall->args[$argumentPosition])) {
            $methodCall->args[$argumentPosition] = new Arg($array);
            return;
        }
        $existingParameterArgValue = $methodCall->args[$argumentPosition]->value;
        if (!$existingParameterArgValue instanceof Array_) {
            // another parameters than array are not suported yet
            throw new NotImplementedYetException();
        }
        $existingParameterArgValue->items = \array_merge($existingParameterArgValue->items, $array->items);
    }
}
