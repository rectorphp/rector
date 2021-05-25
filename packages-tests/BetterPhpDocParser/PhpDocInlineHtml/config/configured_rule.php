<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\Configuration\Option;
use ViewScopeRector\Inferer\Rocket\TestFileLocator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();

    $services->set(\Rector\Tests\BetterPhpDocParser\PhpDocInlineHtml\Source\InlineHtmlRector::class);
};
