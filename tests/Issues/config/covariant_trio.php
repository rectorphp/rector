<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector;
use Rector\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_70);

    $services = $containerConfigurator->services();
<<<<<<< HEAD
<<<<<<< HEAD
    $services->set(\Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\FunctionLike\DowngradeTypeDeclarationRector::class);
    $services->set(\Rector\DowngradePhp70\Rector\Coalesce\DowngradeNullCoalesceRector::class);
=======
    $services->set(DowngradeArrayKeyFirstLastRector::class);
//    $services->set(DowngradeTypeDeclarationRector::class);
    $services->set(DowngradeNullCoalesceRector::class);
>>>>>>> 74e986806 (fixup! add 3 rules that fail covariant downgrade)
=======
    $services->set(DowngradeArrayKeyFirstLastRector::class);
    $services->set(DowngradeTypeDeclarationRector::class);
    $services->set(DowngradeNullCoalesceRector::class);
>>>>>>> f7f08250e (add 3 rules that fail covariant downgrade)
};
