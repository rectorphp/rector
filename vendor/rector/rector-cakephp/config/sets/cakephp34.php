<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

use Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector;
use Rector\CakePHP\ValueObject\ModalToGetSet;
use Rector\Core\ValueObject\Visibility;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;
use Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Rector\Visibility\ValueObject\ChangeMethodVisibility;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
return static function (\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $containerConfigurator) : void {
    $services = $containerConfigurator->services();
    $services->set(\Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector::class)->configure([
        // source: https://book.cakephp.org/3.0/en/appendices/3-4-migration-guide.html
        new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Request', 'params', 'getAttribute', null, ['params']),
        new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Request', 'data', 'getData'),
        new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Request', 'query', 'getQueryParams'),
        new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Request', 'cookies', 'getCookie'),
        new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Request', 'base', 'getAttribute', null, ['base']),
        new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Request', 'webroot', 'getAttribute', null, ['webroot']),
        new \Rector\Transform\ValueObject\PropertyFetchToMethodCall('Cake\\Network\\Request', 'here', 'getAttribute', null, ['here']),
    ]);
    $services->set(\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector::class)->configure([new \Rector\Renaming\ValueObject\RenameProperty('Cake\\Network\\Request', '_session', 'session')]);
    $services->set(\Rector\CakePHP\Rector\MethodCall\ModalToGetSetRector::class)->configure([
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Core\\InstanceConfigTrait', 'config', null, null, 2, 'array'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Core\\StaticConfigTrait', 'config', null, null, 2, 'array'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Console\\ConsoleOptionParser', 'command'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Console\\ConsoleOptionParser', 'description'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Console\\ConsoleOptionParser', 'epilog'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Connection', 'driver'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Connection', 'schemaCollection'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Connection', 'useSavePoints', 'isSavePointsEnabled', 'enableSavePoints'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Driver', 'autoQuoting', 'isAutoQuotingEnabled', 'enableAutoQuoting'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Expression\\FunctionExpression', 'name'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Expression\\QueryExpression', 'tieWith', 'getConjunction', 'setConjunction'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Expression\\ValuesExpression', 'columns'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Expression\\ValuesExpression', 'values'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Expression\\ValuesExpression', 'query'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Query', 'connection'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Query', 'selectTypeMap'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Query', 'bufferResults', 'isBufferedResultsEnabled', 'enableBufferedResults'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Schema\\CachedCollection', 'cacheMetadata'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Schema\\TableSchema', 'options'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\Schema\\TableSchema', 'temporary', 'isTemporary', 'setTemporary'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\TypeMap', 'defaults'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\TypeMap', 'types'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\TypeMapTrait', 'typeMap'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Database\\TypeMapTrait', 'defaultTypes'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'name'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'cascadeCallbacks'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'source'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'target'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'conditions'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'bindingKey'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'foreignKey'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'dependent'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'joinType'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'property'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'strategy'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association', 'finder'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association\\BelongsToMany', 'targetForeignKey'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association\\BelongsToMany', 'saveStrategy'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association\\BelongsToMany', 'conditions'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association\\HasMany', 'saveStrategy'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association\\HasMany', 'foreignKey'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association\\HasMany', 'sort'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Association\\HasOne', 'foreignKey'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\EagerLoadable', 'config'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\EagerLoadable', 'canBeJoined', 'canBeJoined', 'setCanBeJoined'),
        // note: will have to be called after setMatching() to keep the old behavior
        // ref: https://github.com/cakephp/cakephp/blob/4feee5463641e05c068b4d1d31dc5ee882b4240f/src/ORM/EagerLoader.php#L330
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\EagerLoadable', 'matching'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\EagerLoadable', 'autoFields', 'isAutoFieldsEnabled', 'enableAutoFields'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Locator\\TableLocator', 'config'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Query', 'eagerLoader'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Query', 'hydrate', 'isHydrationEnabled', 'enableHydration'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Query', 'autoFields', 'isAutoFieldsEnabled', 'enableAutoFields'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Table', 'table'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Table', 'alias'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Table', 'registryAlias'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Table', 'connection'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Table', 'schema'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Table', 'primaryKey'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Table', 'displayField'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\ORM\\Table', 'entityClass'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'entityClass'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'from'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'sender'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'replyTo'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'readReceipt'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'returnPath'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'to'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'cc'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'bcc'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'charset'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'headerCharset'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'emailPattern'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'subject'),
        // template: have to be changed manually, non A → B change + array case
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'viewRender', 'getViewRenderer', 'setViewRenderer'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'viewVars'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'theme'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'helpers'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'emailFormat'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'transport'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'messageId'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'domain'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'attachments'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'configTransport'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Mailer\\Email', 'profile'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\Validation\\Validator', 'provider'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\StringTemplateTrait', 'templates'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'templatePath'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'layoutPath'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'plugin'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'helpers'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'theme'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'template'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'layout'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'options'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'name'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'className'),
        new \Rector\CakePHP\ValueObject\ModalToGetSet('Cake\\View\\ViewBuilder', 'autoLayout', 'isAutoLayoutEnabled', 'enableAutoLayout'),
    ]);
    $services->set(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class)->configure([
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Request', 'param', 'getParam'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Request', 'data', 'getData'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Request', 'query', 'getQuery'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Request', 'cookie', 'getCookie'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Request', 'method', 'getMethod'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Request', 'setInput', 'withBody'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'location', 'withLocation'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'disableCache', 'withDisabledCache'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'type', 'withType'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'charset', 'withCharset'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'cache', 'withCache'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'modified', 'withModified'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'expires', 'withExpires'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'sharable', 'withSharable'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'maxAge', 'withMaxAge'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'vary', 'withVary'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'etag', 'withEtag'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'compress', 'withCompression'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'length', 'withLength'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'mustRevalidate', 'withMustRevalidate'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'notModified', 'withNotModified'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'cookie', 'withCookie'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'file', 'withFile'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'download', 'withDownload'),
        # psr-7
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'header', 'getHeader'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'body', 'withBody'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'statusCode', 'getStatusCode'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Network\\Response', 'protocol', 'getProtocolVersion'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Event\\Event', 'name', 'getName'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Event\\Event', 'subject', 'getSubject'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Event\\Event', 'result', 'getResult'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Event\\Event', 'data', 'getData'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\View\\Helper\\FormHelper', 'input', 'control'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\View\\Helper\\FormHelper', 'inputs', 'controls'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\View\\Helper\\FormHelper', 'allInputs', 'allControls'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Mailer\\Mailer', 'layout', 'setLayout'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Routing\\Route\\Route', 'parse', 'parseRequest'),
        new \Rector\Renaming\ValueObject\MethodCallRename('Cake\\Routing\\Router', 'parse', 'parseRequest'),
    ]);
    $services->set(\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector::class)->configure([new \Rector\Visibility\ValueObject\ChangeMethodVisibility('Cake\\Mailer\\MailerAwareTrait', 'getMailer', \Rector\Core\ValueObject\Visibility::PROTECTED), new \Rector\Visibility\ValueObject\ChangeMethodVisibility('Cake\\View\\CellTrait', 'cell', \Rector\Core\ValueObject\Visibility::PROTECTED)]);
    $services->set(\Rector\Renaming\Rector\Name\RenameClassRector::class)->configure(['Cake\\Database\\Schema\\Table' => 'Cake\\Database\\Schema\\TableSchema']);
};
