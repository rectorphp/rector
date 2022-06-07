<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
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
