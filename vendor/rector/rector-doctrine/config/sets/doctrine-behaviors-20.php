<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Transform\Rector\Class_\AddInterfaceByTraitRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddInterfaceByTraitRector::class, ['Knp\\DoctrineBehaviors\\Model\\Timestampable\\Timestampable' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\TimestampableInterface', 'Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableMethods' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\TimestampableInterface', 'Knp\\DoctrineBehaviors\\Model\\Blameable\\Blameable' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\BlameableInterface', 'Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableMethods' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\BlameableInterface', 'Knp\\DoctrineBehaviors\\Model\\Loggable\\Loggable' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\LoggableInterface', 'Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletable' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\SoftDeletableInterface', 'Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableMethodsTrait' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\SoftDeletableInterface', 'Knp\\DoctrineBehaviors\\Model\\Translatable\\Translatable' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslatableInterface', 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableMethods' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslatableInterface', 'Knp\\DoctrineBehaviors\\Model\\Translatable\\Translation' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslationInterface', 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationMethods' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslationInterface', 'Knp\\DoctrineBehaviors\\Model\\Uuidable\\Uuidable' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\UuidableInterface', 'Knp\\DoctrineBehaviors\\Model\\Uuidable\\UuidableMethods' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\UuidableInterface']);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # move interface to "Contract"
        'Knp\\DoctrineBehaviors\\Model\\Tree\\NodeInterface' => 'Knp\\DoctrineBehaviors\\Contract\\Entity\\TreeNodeInterface',
        # suffix "Trait" for traits
        'Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableMethods' => 'Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableMethodsTrait',
        'Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableProperties' => 'Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameablePropertiesTrait',
        'Knp\\DoctrineBehaviors\\Model\\Blameable\\Blameable' => 'Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableTrait',
        'Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodableMethods' => 'Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodableMethodsTrait',
        'Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodableProperties' => 'Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodablePropertiesTrait',
        'Knp\\DoctrineBehaviors\\Model\\Geocodable\\Geocodable' => 'Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodableTrait',
        'Knp\\DoctrineBehaviors\\Model\\Loggable\\Loggable' => 'Knp\\DoctrineBehaviors\\Model\\Loggable\\LoggableTrait',
        'Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableMethods' => 'Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableMethodsTrait',
        'Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableProperties' => 'Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggablePropertiesTrait',
        'Knp\\DoctrineBehaviors\\Model\\Sluggable\\Sluggable' => 'Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableTrait',
        'Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableMethods' => 'Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableMethodsTrait',
        'Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableProperties' => 'Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletablePropertiesTrait',
        'Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletable' => 'Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableTrait',
        'Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableMethods' => 'Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableMethodsTrait',
        'Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableProperties' => 'Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampablePropertiesTrait',
        'Knp\\DoctrineBehaviors\\Model\\Timestampable\\Timestampable' => 'Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableTrait',
        'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableMethods' => 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableMethodsTrait',
        'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableProperties' => 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatablePropertiesTrait',
        'Knp\\DoctrineBehaviors\\Model\\Translatable\\Translatable' => 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableTrait',
        'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationMethods' => 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationMethodsTrait',
        'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationProperties' => 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationPropertiesTrait',
        'Knp\\DoctrineBehaviors\\Model\\Translatable\\Translation' => 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationTrait',
        # tree
        'Knp\\DoctrineBehaviors\\Model\\Tree\\Node' => 'Knp\\DoctrineBehaviors\\Model\\Tree\\TreeNodeTrait',
    ]);
    $rectorConfig->ruleWithConfiguration(AddEntityIdByConditionRector::class, ['Knp\\DoctrineBehaviors\\Model\\Translatable\\Translation', 'Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationTrait']);
};
