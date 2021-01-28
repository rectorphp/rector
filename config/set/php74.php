<?php

declare(strict_types=1);

use Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\Double\RealToFloatTypeCastRector;
use Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector;
use Rector\Php74\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use Rector\Php74\Rector\Function_\ReservedFnFunctionRector;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php74\Rector\MethodCall\ChangeReflectionTypeToStringToGetNameRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TypedPropertyRector::class);

    $services->set(RenameFunctionRector::class)
        ->call('configure', [[
            RenameFunctionRector::OLD_FUNCTION_TO_NEW_FUNCTION => [
                #the_real_type
                # https://wiki.php.net/rfc/deprecations_php_7_4
                'is_real' => 'is_float',
                #apache_request_headers_function
                # https://wiki.php.net/rfc/deprecations_php_7_4
                'apache_request_headers' => 'getallheaders',
                //'hebrevc' => ['nl2br', 'hebrev'],
            ],
        ]]);

    $services->set(ArrayKeyExistsOnPropertyRector::class);

    $services->set(FilterVarToAddSlashesRector::class);

    $services->set(ExportToReflectionFunctionRector::class);

    $services->set(GetCalledClassToStaticClassRector::class);

    $services->set(MbStrrposEncodingArgumentPositionRector::class);

    $services->set(RealToFloatTypeCastRector::class);

    $services->set(NullCoalescingOperatorRector::class);

    $services->set(ReservedFnFunctionRector::class);

    $services->set(ClosureToArrowFunctionRector::class);

    $services->set(ArraySpreadInsteadOfArrayMergeRector::class);

    $services->set(AddLiteralSeparatorToNumberRector::class);

    $services->set(ChangeReflectionTypeToStringToGetNameRector::class);

    $services->set(RestoreDefaultNullToNullableTypePropertyRector::class);
};
