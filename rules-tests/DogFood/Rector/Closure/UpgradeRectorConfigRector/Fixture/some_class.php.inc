<?php

namespace Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\Fixture;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PARALLEL, true);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
};

?>
-----
<?php

namespace Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\Fixture;

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (\Rector\Config\RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->importNames();
    $rectorConfig->phpVersion(PhpVersion::PHP_74);
    $rectorConfig->rule(TypedPropertyRector::class);
};

?>
