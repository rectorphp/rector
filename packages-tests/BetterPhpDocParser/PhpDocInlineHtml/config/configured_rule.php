<?php

declare(strict_types=1);

use Rector\Tests\BetterPhpDocParser\PhpDocInlineHtml\Source\InlineHtmlRector;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $parameters = $containerConfigurator->parameters();

    $services->set(InlineHtmlRector::class);
};
