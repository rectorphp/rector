<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Nette\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector;
use RectorPrefix20220606\Rector\Nette\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector;
use RectorPrefix20220606\Rector\Nette\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector;
use RectorPrefix20220606\Rector\Nette\Rector\Assign\MakeGetComponentAssignAnnotatedRector;
use RectorPrefix20220606\Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector;
use RectorPrefix20220606\Rector\Nette\Rector\Identical\SubstrMinusToStringEndsWithRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TemplateMagicAssignToExplicitVariableArrayRector::class);
    $rectorConfig->rule(MakeGetComponentAssignAnnotatedRector::class);
    $rectorConfig->rule(AnnotateMagicalControlArrayAccessRector::class);
    $rectorConfig->rule(ArrayAccessSetControlToAddComponentMethodCallRector::class);
    $rectorConfig->rule(ArrayAccessGetControlToGetComponentMethodCallRector::class);
    $rectorConfig->rule(SubstrMinusToStringEndsWithRector::class);
};
