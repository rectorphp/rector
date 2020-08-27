<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\UnprefixedMethodToGetSet;
use Rector\Generic\Rector\Assign\PropertyToMethodRector;
use Rector\Generic\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Generic\Rector\ClassMethod\NormalToFluentRector;
use Rector\Generic\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Generic\ValueObject\CallToFluent;
use Rector\Generic\ValueObject\MethodVisibility;
use Rector\Generic\ValueObject\PropertyToMethodCall;
use Rector\Generic\ValueObject\RenamedProperty;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use function Rector\SymfonyPhpConfig\inline_value_objects;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(PropertyToMethodRector::class)
        ->call('configure', [[
            PropertyToMethodRector::PROPERTIES_TO_METHOD_CALLS => inline_value_objects([
                // source: https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html
                new PropertyToMethodCall('Cake\Network\Request', 'params', 'getAttribute', null, ['params']),
                new PropertyToMethodCall('Cake\Network\Request', 'data', 'getData'),
                new PropertyToMethodCall('Cake\Network\Request', 'query', 'getQueryParams'),
                new PropertyToMethodCall('Cake\Network\Request', 'cookies', 'getCookie'),
                new PropertyToMethodCall('Cake\Network\Request', 'base', 'getAttribute', null, ['base']),
                new PropertyToMethodCall('Cake\Network\Request', 'webroot', 'getAttribute', null, ['webroot']),
                new PropertyToMethodCall('Cake\Network\Request', 'here', 'getAttribute', null, ['here']),
            ]),
        ]]);

    $services->set(RenamePropertyRector::class)
        ->call('configure', [[
            RenamePropertyRector::RENAMED_PROPERTIES => inline_value_objects([
                new RenamedProperty('Cake\Network\Request', '_session', 'session'),
            ]),
        ]]);

    $services->set(ModalToGetSetRector::class)
        ->call('configure', [[
            ModalToGetSetRector::UNPREFIXED_METHODS_TO_GET_SET => inline_value_objects([
                new UnprefixedMethodToGetSet('Cake\Core\InstanceConfigTrait', 'config', null, null, 2, 'array'),
                new UnprefixedMethodToGetSet('Cake\Core\StaticConfigTrait', 'config', null, null, 2, 'array'),
                new UnprefixedMethodToGetSet('Cake\Console\ConsoleOptionParser', 'command'),
                new UnprefixedMethodToGetSet('Cake\Console\ConsoleOptionParser', 'description'),
                new UnprefixedMethodToGetSet('Cake\Console\ConsoleOptionParser', 'epilog'),
                new UnprefixedMethodToGetSet('Cake\Database\Connection', 'driver'),
                new UnprefixedMethodToGetSet('Cake\Database\Connection', 'schemaCollection'),
                new UnprefixedMethodToGetSet(
                    'Cake\Database\Connection',
                    'useSavePoints',
                    'isSavePointsEnabled',
                    'enableSavePoints'
                ),
                new UnprefixedMethodToGetSet(
                    'Cake\Database\Driver',
                    'autoQuoting',
                    'isAutoQuotingEnabled',
                    'enableAutoQuoting'
                ),
                new UnprefixedMethodToGetSet('Cake\Database\Expression\FunctionExpression', 'name'),
                new UnprefixedMethodToGetSet(
                    'Cake\Database\Expression\QueryExpression',
                    'tieWith',
                    'getConjunction',
                    'setConjunction'
                ),
                new UnprefixedMethodToGetSet('Cake\Database\Expression\ValuesExpression', 'columns'),
                new UnprefixedMethodToGetSet('Cake\Database\Expression\ValuesExpression', 'values'),
                new UnprefixedMethodToGetSet('Cake\Database\Expression\ValuesExpression', 'query'),
                new UnprefixedMethodToGetSet('Cake\Database\Query', 'connection'),
                new UnprefixedMethodToGetSet('Cake\Database\Query', 'selectTypeMap'),
                new UnprefixedMethodToGetSet(
                    'Cake\Database\Query',
                    'bufferResults',
                    'isBufferedResultsEnabled',
                    'enableBufferedResults'
                ),
                new UnprefixedMethodToGetSet('Cake\Database\Schema\CachedCollection', 'cacheMetadata'),
                new UnprefixedMethodToGetSet('Cake\Database\Schema\TableSchema', 'options'),
                new UnprefixedMethodToGetSet(
                    'Cake\Database\Schema\TableSchema',
                    'temporary',
                    'isTemporary',
                    'setTemporary'
                ),
                new UnprefixedMethodToGetSet('Cake\Database\TypeMap', 'defaults'),
                new UnprefixedMethodToGetSet('Cake\Database\TypeMap', 'types'),
                new UnprefixedMethodToGetSet('Cake\Database\TypeMapTrait', 'typeMap'),
                new UnprefixedMethodToGetSet('Cake\Database\TypeMapTrait', 'defaultTypes'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'name'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'cascadeCallbacks'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'source'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'target'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'conditions'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'bindingKey'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'foreignKey'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'dependent'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'joinType'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'property'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'strategy'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association', 'finder'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association\BelongsToMany', 'targetForeignKey'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association\BelongsToMany', 'saveStrategy'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association\BelongsToMany', 'conditions'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association\HasMany', 'saveStrategy'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association\HasMany', 'foreignKey'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association\HasMany', 'sort'),
                new UnprefixedMethodToGetSet('Cake\ORM\Association\HasOne', 'foreignKey'),
                new UnprefixedMethodToGetSet('Cake\ORM\EagerLoadable', 'config'),
                new UnprefixedMethodToGetSet('Cake\ORM\EagerLoadable', 'canBeJoined', 'canBeJoined', 'setCanBeJoined'),

                // note: will have to be called after setMatching() to keep the old behavior
                // ref: https://github.com/cakephp/cakephp/blob/4feee5463641e05c068b4d1d31dc5ee882b4240f/src/ORM/EagerLoader.php#L330
                new UnprefixedMethodToGetSet('Cake\ORM\EagerLoadable', 'matching'),
                new UnprefixedMethodToGetSet(
                    'Cake\ORM\EagerLoadable',
                    'autoFields',
                    'isAutoFieldsEnabled',
                    'enableAutoFields'
                ),
                new UnprefixedMethodToGetSet('Cake\ORM\Locator\TableLocator', 'config'),
                new UnprefixedMethodToGetSet('Cake\ORM\Query', 'eagerLoader'),
                new UnprefixedMethodToGetSet('Cake\ORM\Query', 'hydrate', 'isHydrationEnabled', 'enableHydration'),
                new UnprefixedMethodToGetSet('Cake\ORM\Query', 'autoFields', 'isAutoFieldsEnabled', 'enableAutoFields'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'table'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'alias'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'registryAlias'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'connection'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'schema'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'primaryKey'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'displayField'),
                new UnprefixedMethodToGetSet('Cake\ORM\Table', 'entityClass'),

                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'entityClass'),

                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'from'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'sender'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'replyTo'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'readReceipt'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'returnPath'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'to'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'cc'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'bcc'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'charset'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'headerCharset'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'emailPattern'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'subject'),
                // template: have to be changed manually, non A → B change + array case
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'viewRender', 'getViewRenderer', 'setViewRenderer'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'viewVars'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'theme'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'helpers'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'emailFormat'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'transport'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'messageId'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'domain'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'attachments'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'configTransport'),
                new UnprefixedMethodToGetSet('Cake\Mailer\Email', 'profile'),
                new UnprefixedMethodToGetSet('Cake\Validation\Validator', 'provider'),
                new UnprefixedMethodToGetSet('Cake\View\StringTemplateTrait', 'templates'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'templatePath'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'layoutPath'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'plugin'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'helpers'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'theme'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'template'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'layout'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'options'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'name'),
                new UnprefixedMethodToGetSet('Cake\View\ViewBuilder', 'className'),
                new UnprefixedMethodToGetSet(
                    'Cake\View\ViewBuilder',
                    'autoLayout',
                    'isAutoLayoutEnabled',
                    'enableAutoLayout'
                ),
            ]),
        ]]);

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::METHOD_CALL_RENAMES => inline_value_objects([
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
            ]),
        ]]);

    $services->set(ChangeMethodVisibilityRector::class)
        ->call('configure', [[
            ChangeMethodVisibilityRector::METHOD_VISIBILITIES => inline_value_objects([
                new MethodVisibility('Cake\Mailer\MailerAwareTrait', 'getMailer', 'protected'),
                new MethodVisibility('Cake\View\CellTrait', 'cell', 'protected'),
            ]),
        ]]);

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Cake\Database\Schema\Table' => 'Cake\Database\Schema\TableSchema',
            ],
        ]]);

    $services->set(NormalToFluentRector::class)
        ->call('configure', [[
            NormalToFluentRector::CALLS_TO_FLUENT => inline_value_objects([
                new CallToFluent('Cake\Network\Response', [
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
                ]),
            ]),
        ]]);
};
