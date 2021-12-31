<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Php70\Rector\ClassMethod\Php4ConstructorRector::class);
    $services->set(\Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector::class);
    $services->set(\Rector\Php70\Rector\FuncCall\RandomFunctionRector::class);
    $services->set(\Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector::class);
    $services->set(\Rector\Php70\Rector\FuncCall\MultiDirnameRector::class);
    $services->set(\Rector\Php70\Rector\Assign\ListSplitStringRector::class);
    $services->set(\Rector\Php70\Rector\List_\EmptyListRector::class);
    # be careful, run this just once, since it can keep swapping order back and forth
    $services->set(\Rector\Php70\Rector\Assign\ListSwapArrayOrderRector::class);
    $services->set(\Rector\Php70\Rector\FuncCall\CallUserMethodRector::class);
    $services->set(\Rector\Php70\Rector\FuncCall\EregToPregMatchRector::class);
    $services->set(\Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector::class);
    $services->set(\Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector::class);
    $services->set(\Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector::class);
    $services->set(\Rector\Php70\Rector\If_\IfToSpaceshipRector::class);
    $services->set(\Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector::class);
    $services->set(\Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector::class);
    $services->set(\Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector::class);
    $services->set(\Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector::class);
    $services->set(\Rector\Php70\Rector\FuncCall\NonVariableToVariableOnFunctionCallRector::class);
};
