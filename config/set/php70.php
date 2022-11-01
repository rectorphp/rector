<?php

declare (strict_types=1);
namespace RectorPrefix202211;

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
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(Php4ConstructorRector::class);
    $rectorConfig->rule(TernaryToNullCoalescingRector::class);
    $rectorConfig->rule(RandomFunctionRector::class);
    $rectorConfig->rule(ExceptionHandlerTypehintRector::class);
    $rectorConfig->rule(MultiDirnameRector::class);
    $rectorConfig->rule(ListSplitStringRector::class);
    $rectorConfig->rule(EmptyListRector::class);
    # be careful, run this just once, since it can keep swapping order back and forth
    $rectorConfig->rule(ListSwapArrayOrderRector::class);
    $rectorConfig->rule(CallUserMethodRector::class);
    $rectorConfig->rule(EregToPregMatchRector::class);
    $rectorConfig->rule(ReduceMultipleDefaultSwitchRector::class);
    $rectorConfig->rule(TernaryToSpaceshipRector::class);
    $rectorConfig->rule(WrapVariableVariableNameInCurlyBracesRector::class);
    $rectorConfig->rule(IfToSpaceshipRector::class);
    $rectorConfig->rule(StaticCallOnNonStaticToInstanceCallRector::class);
    $rectorConfig->rule(ThisCallOnStaticMethodToStaticCallRector::class);
    $rectorConfig->rule(BreakNotInLoopOrSwitchToReturnRector::class);
    $rectorConfig->rule(RenameMktimeWithoutArgsToTimeRector::class);
    $rectorConfig->rule(NonVariableToVariableOnFunctionCallRector::class);
};
