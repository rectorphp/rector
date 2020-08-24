<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\Generic\Rector\Assign\PropertyToMethodRector;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\Rector\ClassMethod\NormalToFluentRector;
use Rector\Generic\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyToMethodRector::class)
        ->call('configure', [[
            PropertyToMethodRector::PER_CLASS_PROPERTY_TO_METHODS => [
                'Cake\Network\Request' => [
                    'params' => [
                        'get' => [
                            # source: https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html
                            'method' => 'getAttribute',
                            'arguments' => ['params'],
                        ],
                    ],
                    'data' => [
                        'get' => 'getData',
                    ],
                    'query' => [
                        'get' => 'getQueryParams',
                    ],
                    'cookies' => [
                        'get' => 'getCookie',
                    ],
                    'base' => [
                        'get' => [
                            'method' => 'getAttribute',
                            'arguments' => ['base'],
                        ],
                    ],
                    'webroot' => [
                        'get' => [
                            'method' => 'getAttribute',
                            'arguments' => ['webroot'],
                        ],
                    ],
                    'here' => [
                        'get' => [
                            'method' => 'getAttribute',
                            'arguments' => ['here'],
                        ],
                    ],
                ],
            ],
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::OLD_TO_NEW_PROPERTY_BY_TYPES => [
                'Cake\Network\Request' => [
                    '_session' => 'session',
                ],
            ],
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::METHOD_NAMES_BY_TYPES => [
                'Cake\Core\InstanceConfigTrait' => [
                    'config' => [
                        'minimal_argument_count' => 2,
                        'first_argument_type_to_set' => 'array',
                    ],
                ],
                'Cake\Core\StaticConfigTrait' => [
                    'config' => [
                        'minimal_argument_count' => 2,
                        'first_argument_type_to_set' => 'array',
                    ],
                    'dsnClassMap' => null,
                ],
                'Cake\Console\ConsoleOptionParser' => [
                    'command' => null,
                    'description' => null,
                    'epilog' => null,
                ],
                'Cake\Database\Connection' => [
                    'driver' => null,
                    'schemaCollection' => null,
                    'useSavePoints' => [
                        'set' => 'enableSavePoints',
                        'get' => 'isSavePointsEnabled',
                    ],
                ],
                'Cake\Database\Driver' => [
                    'autoQuoting' => [
                        'set' => 'enableAutoQuoting',
                        'get' => 'isAutoQuotingEnabled',
                    ],
                ],
                'Cake\Database\Expression\FunctionExpression' => [
                    'name' => null,
                ],
                'Cake\Database\Expression\QueryExpression' => [
                    'tieWith' => [
                        'set' => 'setConjunction',
                        'get' => 'getConjunction',
                    ],
                ],
                'Cake\Database\Expression\ValuesExpression' => [
                    'columns' => null,
                    'values' => null,
                    'query' => null,
                ],
                'Cake\Database\Query' => [
                    'connection' => null,
                    'selectTypeMap' => null,
                    'bufferResults' => [
                        'set' => 'enableBufferedResults',
                        'get' => 'isBufferedResultsEnabled',
                    ],
                ],
                'Cake\Database\Schema\CachedCollection' => [
                    'cacheMetadata' => null,
                ],
                'Cake\Database\Schema\TableSchema' => [
                    'options' => null,
                    'temporary' => [
                        'set' => 'setTemporary',
                        'get' => 'isTemporary',
                    ],
                ],
                'Cake\Database\TypeMap' => [
                    'defaults' => null,
                    'types' => null,
                ],
                'Cake\Database\TypeMapTrait' => [
                    'typeMap' => null,
                    'defaultTypes' => null,
                ],
                'Cake\ORM\Association' => [
                    'name' => null,
                    'cascadeCallbacks' => null,
                    'source' => null,
                    'target' => null,
                    'conditions' => null,
                    'bindingKey' => null,
                    'foreignKey' => null,
                    'dependent' => null,
                    'joinType' => null,
                    'property' => null,
                    'strategy' => null,
                    'finder' => null,
                ],
                'Cake\ORM\Association\BelongsToMany' => [
                    'targetForeignKey' => null,
                    'saveStrategy' => null,
                    'conditions' => null,
                ],
                'Cake\ORM\Association\HasMany' => [
                    'saveStrategy' => null,
                    'foreignKey' => null,
                    'sort' => null,
                ],
                'Cake\ORM\Association\HasOne' => [
                    'foreignKey' => null,
                ],
                'Cake\ORM\EagerLoadable' => [
                    'config' => null,
                    'canBeJoined' => [
                        'set' => 'setCanBeJoined',
                        'get' => 'canBeJoined',
                    ],
                ],
                'Cake\ORM\EagerLoader' => [
                    # note: will have to be called after setMatching() to keep the old behavior
                    # ref: https://github.com/cakephp/cakephp/blob/4feee5463641e05c068b4d1d31dc5ee882b4240f/src/ORM/EagerLoader.php#L330
                    'matching' => [
                        'set' => 'setMatching',
                        'get' => 'getMatching',
                    ],
                    'autoFields' => [
                        'set' => 'enableAutoFields',
                        'get' => 'isAutoFieldsEnabled',
                    ],
                ],
                'Cake\ORM\Locator\TableLocator' => [
                    'config' => null,
                ],
                'Cake\ORM\Query' => [
                    'eagerLoader' => null,
                    'hydrate' => [
                        'set' => 'enableHydration',
                        'get' => 'isHydrationEnabled',
                    ],
                    'autoFields' => [
                        'set' => 'enableAutoFields',
                        'get' => 'isAutoFieldsEnabled',
                    ],
                ],
                'Cake\ORM\Table' => [
                    'table' => null,
                    'alias' => null,
                    'registryAlias' => null,
                    'connection' => null,
                    'schema' => null,
                    'primaryKey' => null,
                    'displayField' => null,
                    'entityClass' => null,
                ],
                'Cake\Mailer\Email' => [
                    'from' => null,
                    'sender' => null,
                    'replyTo' => null,
                    'readReceipt' => null,
                    'returnPath' => null,
                    'to' => null,
                    'cc' => null,
                    'bcc' => null,
                    'charset' => null,
                    'headerCharset' => null,
                    'emailPattern' => null,
                    'subject' => null,
                    'viewRender' => [
                        # template: have to be changed manually, non A → B change + array case
                        'set' => 'setViewRenderer',
                        'get' => 'getViewRenderer',
                    ],
                    'viewVars' => null,
                    'theme' => null,
                    'helpers' => null,
                    'emailFormat' => null,
                    'transport' => null,
                    'messageId' => null,
                    'domain' => null,
                    'attachments' => null,
                    'configTransport' => null,
                    'profile' => null,
                ],
                'Cake\Validation\Validator' => [
                    'provider' => null,
                ],
                'Cake\View\StringTemplateTrait' => [
                    'templates' => null,
                ],
                'Cake\View\ViewBuilder' => [
                    'templatePath' => null,
                    'layoutPath' => null,
                    'plugin' => null,
                    'helpers' => null,
                    'theme' => null,
                    'template' => null,
                    'layout' => null,
                    'options' => null,
                    'name' => null,
                    'className' => null,
                    'autoLayout' => [
                        'set' => 'enableAutoLayout',
                        'get' => 'isAutoLayoutEnabled',
                    ],
                ],
            ],
        ]]);

    $configuration = [
        new MethodCallRename('Cake\Network\Request', 'param', 'getParam'),
        new MethodCallRename('Cake\Network\Request', 'data', 'getData'),
        new MethodCallRename('Cake\Network\Request', 'query', 'getQuery'),
        new MethodCallRename('Cake\Network\Request', 'cookie', 'getCookie'),
        new MethodCallRename('Cake\Network\Request', 'method', 'getMethod'),
        new MethodCallRename('Cake\Network\Request', 'setInput', 'withBody'),
        new MethodCallRename('Cake\Network\Response', 'location', 'withLocation'),
        new MethodCallRename('Cake\Network\Response', 'disableCache', 'withDisabledCache'),
        new MethodCallRename('Cake\Network\Response', 'type', 'withType'),
        new MethodCallRename('Cake\Network\Response', 'charset', 'withCharset'),
        new MethodCallRename('Cake\Network\Response', 'cache', 'withCache'),
        new MethodCallRename('Cake\Network\Response', 'modified', 'withModified'),
        new MethodCallRename('Cake\Network\Response', 'expires', 'withExpires'),
        new MethodCallRename('Cake\Network\Response', 'sharable', 'withSharable'),
        new MethodCallRename('Cake\Network\Response', 'maxAge', 'withMaxAge'),
        new MethodCallRename('Cake\Network\Response', 'vary', 'withVary'),
        new MethodCallRename('Cake\Network\Response', 'etag', 'withEtag'),
        new MethodCallRename('Cake\Network\Response', 'compress', 'withCompression'),
        new MethodCallRename('Cake\Network\Response', 'length', 'withLength'),
        new MethodCallRename('Cake\Network\Response', 'mustRevalidate', 'withMustRevalidate'),
        new MethodCallRename('Cake\Network\Response', 'notModified', 'withNotModified'),
        new MethodCallRename('Cake\Network\Response', 'cookie', 'withCookie'),
        new MethodCallRename('Cake\Network\Response', 'file', 'withFile'),
        new MethodCallRename('Cake\Network\Response', 'download', 'withDownload'),
        # psr-7
        new MethodCallRename('Cake\Network\Response', 'header', 'getHeader'),
        new MethodCallRename('Cake\Network\Response', 'body', 'withBody'),
        new MethodCallRename('Cake\Network\Response', 'statusCode', 'getStatusCode'),
        new MethodCallRename('Cake\Network\Response', 'protocol', 'getProtocolVersion'),
        new MethodCallRename('Cake\Event\Event', 'name', 'getName'),
        new MethodCallRename('Cake\Event\Event', 'subject', 'getSubject'),
        new MethodCallRename('Cake\Event\Event', 'result', 'getResult'),
        new MethodCallRename('Cake\Event\Event', 'data', 'getData'),
        new MethodCallRename('Cake\View\Helper\FormHelper', 'input', 'control'),
        new MethodCallRename('Cake\View\Helper\FormHelper', 'inputs', 'controls'),
        new MethodCallRename('Cake\View\Helper\FormHelper', 'allInputs', 'allControls'),
        new MethodCallRename('Cake\Mailer\Mailer', 'layout', 'setLayout'),
        new MethodCallRename('Cake\Routing\Route\Route', 'parse', 'parseRequest'),
        new MethodCallRename('Cake\Routing\Router', 'parse', 'parseRequest'),
    ];
    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects($configuration),
        ]]);

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_TO_VISIBILITY_BY_CLASS => [
                'Cake\Mailer\MailerAwareTrait' => [
                    'getMailer' => 'protected',
                ],
                'Cake\View\CellTrait' => [
                    'cell' => 'protected',
                ],
            ],
        ]]);

    $services->set(RenameClassRector::class)
        ->call(
            'configure',
            [[
                RenameClassRector::OLD_TO_NEW_CLASSES => [
                    'Cake\Database\Schema\Table' => 'Cake\Database\Schema\TableSchema',
                ],
            ]]
        );

    $services->set(NormalToFluentRector::class)
        ->call('configure', [[
            NormalToFluentRector::FLUENT_METHODS_BY_TYPE => [
                'Cake\Network\Response' => [
                    'withLocation',
                    'withHeader',
                    'withDisabledCache',
                    'withType',
                    'withCharset',
                    'withCache',
                    'withModified',
                    'withExpires',
                    'withSharable',
                    'withMaxAge',
                    'withVary',
                    'withEtag',
                    'withCompression',
                    'withLength',
                    'withMustRevalidate',
                    'withNotModified',
                    'withCookie',
                    'withFile',
                    'withDownload',
                ],
            ],
        ]]);
};
