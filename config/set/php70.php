<?php

declare (strict_types=1);
namespace RectorPrefix20220606;

use RectorPrefix20220606\Rector\Config\RectorConfig;
use RectorPrefix20220606\Rector\Php70\Rector\Assign\ListSplitStringRector;
use RectorPrefix20220606\Rector\Php70\Rector\Assign\ListSwapArrayOrderRector;
use RectorPrefix20220606\Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;
use RectorPrefix20220606\Rector\Php70\Rector\ClassMethod\Php4ConstructorRector;
use RectorPrefix20220606\Rector\Php70\Rector\FuncCall\CallUserMethodRector;
use RectorPrefix20220606\Rector\Php70\Rector\FuncCall\EregToPregMatchRector;
use RectorPrefix20220606\Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use RectorPrefix20220606\Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector;
use RectorPrefix20220606\Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use RectorPrefix20220606\Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector;
use RectorPrefix20220606\Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector;
use RectorPrefix20220606\Rector\Php70\Rector\If_\IfToSpaceshipRector;
use RectorPrefix20220606\Rector\Php70\Rector\List_\EmptyListRector;
use RectorPrefix20220606\Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use RectorPrefix20220606\Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use RectorPrefix20220606\Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector;
use RectorPrefix20220606\Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use RectorPrefix20220606\Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector;
use RectorPrefix20220606\Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector;
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
