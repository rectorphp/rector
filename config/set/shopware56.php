<?php

declare(strict_types=1);

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    # See https://github.com/shopware/shopware/blob/5.6/UPGRADE-5.6.md
    $containerConfigurator->import(__DIR__ . '/elasticsearch-dsl50.php');

    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
                new MethodCallRename('Enlight_Controller_Response_Response', 'getHttpResponseCode', 'getStatusCode'),
                new MethodCallRename('Enlight_Controller_Response_Response', 'setHttpResponseCode', 'setStatusCode'),
                new MethodCallRename('Enlight_Controller_Response_Response', 'sendCookies', 'sendHeaders'),
                new MethodCallRename('Enlight_Controller_Response_Response', 'setBody', 'setContent'),
            ]),
        ]]);
};
