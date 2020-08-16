<?php

declare(strict_types=1);

use Rector\Generic\Rector\Assign\PropertyToMethodRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    # source: https://book.cakephp.org/3.0/en/appendices/3-6-migration-guide.html
    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Cake\ORM\Table' => [
                    'association' => 'getAssociation',
                ],
                'Cake\Validation\ValidationSet' => [
                    'isPresenceRequired' => 'requirePresence',
                    'isEmptyAllowed' => 'allowEmpty',
                ],
            ],
        ]]);

    $services->set(PropertyToMethodRector::class)
        ->call('configure', [[
            PropertyToMethodRector::PER_CLASS_PROPERTY_TO_METHODS => [
                'Cake\Controller\Controller' => [
                    'name' => [
                        'get' => 'getName',
                        'set' => 'setName',
                    ],
                    'plugin' => [
                        'get' => 'getPlugin',
                        'set' => 'setPlugin',
                    ],
                ],
                'Cake\Form\Form' => [
                    'validator' => [
                        'get' => 'getValidator',
                        'set' => 'setValidator',
                    ],
                ],
            ],
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Cake\Cache\Engine\ApcEngine' => 'Cake\Cache\Engine\ApcuEngine',
                'Cake\Network\Exception\BadRequestException' => 'Cake\Http\Exception\BadRequestException',
                'Cake\Network\Exception\ConflictException' => 'Cake\Http\Exception\ConflictException',
                'Cake\Network\Exception\ForbiddenException' => 'Cake\Http\Exception\ForbiddenException',
                'Cake\Network\Exception\GoneException' => 'Cake\Http\Exception\GoneException',
                'Cake\Network\Exception\HttpException' => 'Cake\Http\Exception\HttpException',
                'Cake\Network\Exception\InternalErrorException' => 'Cake\Http\Exception\InternalErrorException',
                'Cake\Network\Exception\InvalidCsrfTokenException' => 'Cake\Http\Exception\InvalidCsrfTokenException',
                'Cake\Network\Exception\MethodNotAllowedException' => 'Cake\Http\Exception\MethodNotAllowedException',
                'Cake\Network\Exception\NotAcceptableException' => 'Cake\Http\Exception\NotAcceptableException',
                'Cake\Network\Exception\NotFoundException' => 'Cake\Http\Exception\NotFoundException',
                'Cake\Network\Exception\NotImplementedException' => 'Cake\Http\Exception\NotImplementedException',
                'Cake\Network\Exception\ServiceUnavailableException' => 'Cake\Http\Exception\ServiceUnavailableException',
                'Cake\Network\Exception\UnauthorizedException' => 'Cake\Http\Exception\UnauthorizedException',
                'Cake\Network\Exception\UnavailableForLegalReasonsException' => 'Cake\Http\Exception\UnavailableForLegalReasonsException',
                'Cake\Network\Session' => 'Cake\Http\Session',
                'Cake\Network\Session\DatabaseSession' => 'Cake\Http\Session\DatabaseSession',
                'Cake\Network\Session\CacheSession' => 'Cake\Http\Session\CacheSession',
                'Cake\Network\CorsBuilder' => 'Cake\Http\CorsBuilder',
                'Cake\View\Widget\WidgetRegistry' => 'Cake\View\Widget\WidgetLocator',
            ],
        ]]);
};
