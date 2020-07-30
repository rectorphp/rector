<?php

declare(strict_types=1);

use Rector\MagicDisclosure\Rector\Assign\GetAndSetToMethodCallRector;
use Rector\MagicDisclosure\Rector\Isset_\UnsetAndIssetToMethodCallRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(GetAndSetToMethodCallRector::class)
        ->call('configure', [[
            GetAndSetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
                'Enlight_View_Default' => [
                    # See https://github.com/shopware/shopware/blob/5.5/UPGRADE-5.5.md
                    'get' => 'getAssign',
                    'set' => 'assign',
                ],
                'Enlight_Components_Session_Namespace' => [
                    'get' => 'get',
                    'set' => 'offsetSet',
                ],
                'Shopware_Components_Config' => [
                    'get' => 'offsetGet',
                    'set' => 'offsetSet',
                ],
            ],
        ]]);

    $services->set(UnsetAndIssetToMethodCallRector::class)
        ->call('configure', [[
            UnsetAndIssetToMethodCallRector::TYPE_TO_METHOD_CALLS => [
                'Enlight_Components_Session_Namespace' => [
                    'isset' => 'offsetExists',
                    'unset' => 'offsetUnset',
                ],
                'Shopware_Components_Config' => [
                    'isset' => 'offsetExists',
                    'unset' => 'offsetUnset',
                ],
            ],
        ]]);
};
