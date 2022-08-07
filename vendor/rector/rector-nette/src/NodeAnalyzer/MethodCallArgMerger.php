<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Exception\NotImplementedYetException;
final class MethodCallArgMerger
{
    public function mergeOrApendArray(MethodCall $methodCall, int $argumentPosition, Array_ $array) : void
    {
        $methodCallArgs = $methodCall->getArgs();
        if (!isset($methodCallArgs[$argumentPosition])) {
            $methodCall->args[$argumentPosition] = new Arg($array);
            return;
        }
        $existingParameterArgValue = $methodCallArgs[$argumentPosition]->value;
        if (!$existingParameterArgValue instanceof Array_) {
            // another parameters than array are not suported yet
            throw new NotImplementedYetException();
        }
        $existingParameterArgValue->items = \array_merge($existingParameterArgValue->items, $array->items);
    }
}
