<?php

declare (strict_types=1);
namespace RectorPrefix202208;

use Rector\Config\RectorConfig;
use Rector\Nette\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector;
use Rector\Nette\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector;
use Rector\Nette\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector;
use Rector\Nette\Rector\Assign\MakeGetComponentAssignAnnotatedRector;
use Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector;
use Rector\Nette\Rector\Identical\SubstrMinusToStringEndsWithRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TemplateMagicAssignToExplicitVariableArrayRector::class);
    $rectorConfig->rule(MakeGetComponentAssignAnnotatedRector::class);
    $rectorConfig->rule(AnnotateMagicalControlArrayAccessRector::class);
    $rectorConfig->rule(ArrayAccessSetControlToAddComponentMethodCallRector::class);
    $rectorConfig->rule(ArrayAccessGetControlToGetComponentMethodCallRector::class);
    $rectorConfig->rule(SubstrMinusToStringEndsWithRector::class);
};
