<?php

declare (strict_types=1);
namespace RectorPrefix202207;

use Rector\Core\Contract\Rector\RectorInterface;
use Rector\PHPUnit\Naming\TestClassNameResolverInterface;
use Rector\Set\Contract\SetListInterface;
use RectorPrefix202207\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use RectorPrefix202207\Symplify\EasyCI\ValueObject\Option;
return static function (ContainerConfigurator $containerConfigurator) : void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::TYPES_TO_SKIP, [TestClassNameResolverInterface::class, RectorInterface::class, SetListInterface::class]);
};
