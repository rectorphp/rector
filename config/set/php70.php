<?php

declare(strict_types=1);

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

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/mysql-to-mysqli.php');

    $services = $containerConfigurator->services();

    $services->set(Php4ConstructorRector::class);

    $services->set(TernaryToNullCoalescingRector::class);

    $services->set(RandomFunctionRector::class);

    $services->set(ExceptionHandlerTypehintRector::class);

    $services->set(MultiDirnameRector::class);

    $services->set(ListSplitStringRector::class);

    $services->set(EmptyListRector::class);

    # be careful, run this just once, since it can keep swapping order back and forth
    $services->set(ListSwapArrayOrderRector::class);

    $services->set(CallUserMethodRector::class);

    $services->set(EregToPregMatchRector::class);

    $services->set(ReduceMultipleDefaultSwitchRector::class);

    $services->set(TernaryToSpaceshipRector::class);

    $services->set(WrapVariableVariableNameInCurlyBracesRector::class);

    $services->set(IfToSpaceshipRector::class);

    $services->set(StaticCallOnNonStaticToInstanceCallRector::class);

    $services->set(ThisCallOnStaticMethodToStaticCallRector::class);

    $services->set(BreakNotInLoopOrSwitchToReturnRector::class);

    $services->set(RenameMktimeWithoutArgsToTimeRector::class);

    $services->set(NonVariableToVariableOnFunctionCallRector::class);
};
