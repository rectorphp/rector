<?php

declare (strict_types=1);
namespace RectorPrefix20220531;

use Rector\Config\RectorConfig;
use Rector\Php70\Rector\Assign\ListSplitStringRector;
use Rector\Php70\Rector\Assign\ListSwapArrayOrderRector;
use Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;
use Rector\Php70\Rector\ClassMethod\Php4ConstructorRector;
use Rector\Php70\Rector\FuncCall\CallUserMethodRector;
use Rector\Php70\Rector\FuncCall\EregToPregMatchRector;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector;
use Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector;
use Rector\Php70\Rector\If_\IfToSpaceshipRector;
use Rector\Php70\Rector\List_\EmptyListRector;
use Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector;
use Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(\Rector\Php70\Rector\ClassMethod\Php4ConstructorRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\FuncCall\RandomFunctionRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\FuncCall\MultiDirnameRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\Assign\ListSplitStringRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\List_\EmptyListRector::class);
    # be careful, run this just once, since it can keep swapping order back and forth
    $rectorConfig->rule(\Rector\Php70\Rector\Assign\ListSwapArrayOrderRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\FuncCall\CallUserMethodRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\FuncCall\EregToPregMatchRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\If_\IfToSpaceshipRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector::class);
    $rectorConfig->rule(\Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector::class);
};
