<?php

declare(strict_types=1);
use Rector\Downgrade\Rector\LNumber\ChangePhpVersionInPlatformCheckRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionToConstructorPropertyAssignRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeReturnDeclarationRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeParamDeclarationRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeParamMixedTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnMixedTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\FunctionLike\DowngradeReturnStaticTypeDeclarationRector::class);
    $services->set(\Rector\Downgrade\Rector\LNumber\ChangePhpVersionInPlatformCheckRector::class)->call('configure', [[\Rector\Downgrade\Rector\LNumber\ChangePhpVersionInPlatformCheckRector::TARGET_PHP_VERSION => 80000]]);
    $services->set(\Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionToConstructorPropertyAssignRector::class);
    $services->set(\Rector\DowngradePhp80\Rector\Class_\DowngradeThrowExpressionRector::class);
};
