<?php

namespace Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import('any-config.php');
};

?>
-----
<?php

namespace Rector\Tests\DogFood\Rector\Closure\UpgradeRectorConfigRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (\Rector\Config\RectorConfig $rectorConfig): void {
    $rectorConfig->import('any-config.php');
};

?>
