<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Nette\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector;
use Rector\Nette\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector;
use Rector\Nette\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector;
use Rector\Nette\Rector\Assign\MakeGetComponentAssignAnnotatedRector;
use Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector;
use Rector\Nette\Rector\Identical\SubstrMinusToStringEndsWithRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Nette\Rector\ClassMethod\TemplateMagicAssignToExplicitVariableArrayRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\Assign\MakeGetComponentAssignAnnotatedRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\ArrayDimFetch\AnnotateMagicalControlArrayAccessRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\Assign\ArrayAccessSetControlToAddComponentMethodCallRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\Assign\ArrayAccessGetControlToGetComponentMethodCallRector::class);
    $rectorConfig->rule(\Rector\Nette\Rector\Identical\SubstrMinusToStringEndsWithRector::class);
};
