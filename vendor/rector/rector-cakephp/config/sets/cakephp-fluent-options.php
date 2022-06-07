<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use Rector\CakePHP\ValueObject\FactoryMethod;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ArrayToFluentCallRector::class, [ArrayToFluentCallRector::ARRAYS_TO_FLUENT_CALLS => [new ArrayToFluentCall('RectorPrefix20220607\\Cake\\ORM\\Association', [
        'bindingKey' => 'setBindingKey',
        'cascadeCallbacks' => 'setCascadeCallbacks',
        'className' => 'setClassName',
        'conditions' => 'setConditions',
        'dependent' => 'setDependent',
        'finder' => 'setFinder',
        'foreignKey' => 'setForeignKey',
        'joinType' => 'setJoinType',
        'propertyName' => 'setProperty',
        'sourceTable' => 'setSource',
        'strategy' => 'setStrategy',
        'targetTable' => 'setTarget',
        # BelongsToMany and HasMany only
        'saveStrategy' => 'setSaveStrategy',
        'sort' => 'setSort',
        # BelongsToMany only
        'targetForeignKey' => 'setTargetForeignKey',
        'through' => 'setThrough',
    ]), new ArrayToFluentCall('RectorPrefix20220607\\Cake\\ORM\\Query', ['fields' => 'select', 'conditions' => 'where', 'join' => 'join', 'order' => 'order', 'limit' => 'limit', 'offset' => 'offset', 'group' => 'group', 'having' => 'having', 'contain' => 'contain', 'page' => 'page']), new ArrayToFluentCall('RectorPrefix20220607\\Cake\\ORM\\Association', [
        'bindingKey' => 'setBindingKey',
        'cascadeCallbacks' => 'setCascadeCallbacks',
        'className' => 'setClassName',
        'conditions' => 'setConditions',
        'dependent' => 'setDependent',
        'finder' => 'setFinder',
        'foreignKey' => 'setForeignKey',
        'joinType' => 'setJoinType',
        'propertyName' => 'setProperty',
        'sourceTable' => 'setSource',
        'strategy' => 'setStrategy',
        'targetTable' => 'setTarget',
        # BelongsToMany and HasMany only
        'saveStrategy' => 'setSaveStrategy',
        'sort' => 'setSort',
        # BelongsToMany only
        'targetForeignKey' => 'setTargetForeignKey',
        'through' => 'setThrough',
    ]), new ArrayToFluentCall('RectorPrefix20220607\\Cake\\ORM\\Query', ['fields' => 'select', 'conditions' => 'where', 'join' => 'join', 'order' => 'order', 'limit' => 'limit', 'offset' => 'offset', 'group' => 'group', 'having' => 'having', 'contain' => 'contain', 'page' => 'page'])], ArrayToFluentCallRector::FACTORY_METHODS => [new FactoryMethod('RectorPrefix20220607\\Cake\\ORM\\Table', 'belongsTo', 'RectorPrefix20220607\\Cake\\ORM\\Association', 2), new FactoryMethod('RectorPrefix20220607\\Cake\\ORM\\Table', 'belongsToMany', 'RectorPrefix20220607\\Cake\\ORM\\Association', 2), new FactoryMethod('RectorPrefix20220607\\Cake\\ORM\\Table', 'hasMany', 'RectorPrefix20220607\\Cake\\ORM\\Association', 2), new FactoryMethod('RectorPrefix20220607\\Cake\\ORM\\Table', 'hasOne', 'RectorPrefix20220607\\Cake\\ORM\\Association', 2), new FactoryMethod('RectorPrefix20220607\\Cake\\ORM\\Table', 'find', 'RectorPrefix20220607\\Cake\\ORM\\Query', 2)]]);
};
