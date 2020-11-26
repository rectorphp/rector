<?php

declare(strict_types=1);

use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\ValueObject\ArrayToFluentCall;
use Rector\CakePHP\ValueObject\FactoryMethod;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ArrayToFluentCallRector::class)
        ->call('configure', [[
            ArrayToFluentCallRector::ARRAYS_TO_FLUENT_CALLS => ValueObjectInliner::inline([
                new ArrayToFluentCall('Cake\ORM\Association', [
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
                ]),
                new ArrayToFluentCall('Cake\ORM\Query', [
                    'fields' => 'select',
                    'conditions' => 'where',
                    'join' => 'join',
                    'order' => 'order',
                    'limit' => 'limit',
                    'offset' => 'offset',
                    'group' => 'group',
                    'having' => 'having',
                    'contain' => 'contain',
                    'page' => 'page',
                ]),
                new ArrayToFluentCall('Cake\ORM\Association', [
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
                ]),
                new ArrayToFluentCall('Cake\ORM\Query', [
                    'fields' => 'select',
                    'conditions' => 'where',
                    'join' => 'join',
                    'order' => 'order',
                    'limit' => 'limit',
                    'offset' => 'offset',
                    'group' => 'group',
                    'having' => 'having',
                    'contain' => 'contain',
                    'page' => 'page',
                ]),
            ]),
            ArrayToFluentCallRector::FACTORY_METHODS => ValueObjectInliner::inline([
                new FactoryMethod('Cake\ORM\Table', 'belongsTo', 'Cake\ORM\Association', 2),
                new FactoryMethod('Cake\ORM\Table', 'belongsToMany', 'Cake\ORM\Association', 2),
                new FactoryMethod('Cake\ORM\Table', 'hasMany', 'Cake\ORM\Association', 2),
                new FactoryMethod('Cake\ORM\Table', 'hasOne', 'Cake\ORM\Association', 2),
                new FactoryMethod('Cake\ORM\Table', 'find', 'Cake\ORM\Query', 2),
            ]),
        ]]);
};
