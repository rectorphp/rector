<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
final class FormOptionsArrayMatcher
{
    public function match(MethodCall $methodCall) : ?Array_
    {
        if (!isset($methodCall->getArgs()[2])) {
            return null;
        }
        $optionsArray = $methodCall->getArgs()[2]->value;
        if (!$optionsArray instanceof Array_) {
            return null;
        }
        return $optionsArray;
    }
}
