<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp80\Rector\Catch_\DowngradeNonCapturingCatchesRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector;
use Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector;
use Rector\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeStaticTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector;
use Rector\DowngradePhp80\Rector\Expression\DowngradeMatchToSwitchRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrContainsRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrEndsWithRector;
use Rector\DowngradePhp80\Rector\FuncCall\DowngradeStrStartsWithRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector;
use Rector\DowngradePhp80\Rector\MethodCall\DowngradeNamedArgumentRector;
use Rector\DowngradePhp80\Rector\NullsafeMethodCall\DowngradeNullsafeToTernaryOperatorRector;
use Rector\DowngradePhp80\Rector\Property\DowngradeUnionTypeTypedPropertyRector;
use Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Rector\Removing\Rector\Class_\RemoveInterfacesRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    $services = $containerConfigurator->services();
    $services->set(RemoveInterfacesRector::class)
        ->call('configure', [[
            RemoveInterfacesRector::INTERFACES_TO_REMOVE => [
                // @see https://wiki.php.net/rfc/stringable
                'Stringable',
            ],
        ]]);

    $services->set(DowngradeNamedArgumentRector::class);

    $services->set(DowngradeAttributeToAnnotationRector::class)
        ->call('configure', [[
            DowngradeAttributeToAnnotationRector::ATTRIBUTE_TO_ANNOTATION => ValueObjectInliner::inline([
                // Symfony
                new DowngradeAttributeToAnnotation('Symfony\Contracts\Service\Attribute\Required', 'required'),
                // Nette
                new DowngradeAttributeToAnnotation('Nette\DI\Attributes\Inject', 'inject'),
            ]),
        ]]);

    $services->set(DowngradeUnionTypeTypedPropertyRector::class);
    $services->set(DowngradeUnionTypeDeclarationRector::class);
    $services->set(DowngradeMixedTypeDeclarationRector::class);
    $services->set(DowngradeStaticTypeDeclarationRector::class);
    $services->set(DowngradePropertyPromotionRector::class);
    $services->set(DowngradeNonCapturingCatchesRector::class);
    $services->set(DowngradeStrContainsRector::class);
    $services->set(DowngradeMatchToSwitchRector::class);
    $services->set(DowngradeClassOnObjectToGetClassRector::class);
    $services->set(DowngradeNullsafeToTernaryOperatorRector::class);
    $services->set(DowngradeTrailingCommasInParamUseRector::class);
    $services->set(DowngradeStrStartsWithRector::class);
    $services->set(DowngradeStrEndsWithRector::class);
};
