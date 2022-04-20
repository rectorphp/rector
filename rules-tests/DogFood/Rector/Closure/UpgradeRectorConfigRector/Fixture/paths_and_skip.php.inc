<?php

namespace Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\Fixture;

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SKIP, []);
    $parameters->set(Option::PATHS, []);
};

?>
-----
<?php

namespace Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\Fixture;

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (\Rector\Config\RectorConfig $rectorConfig): void {
    $rectorConfig->skip([]);
    $rectorConfig->paths([]);
};

?>
