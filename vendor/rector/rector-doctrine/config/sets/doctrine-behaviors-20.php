<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use Rector\Config\RectorConfig;
use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Transform\Rector\Class_\AddInterfaceByTraitRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddInterfaceByTraitRector::class, ['RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Timestampable\\Timestampable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\TimestampableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\TimestampableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Blameable\\Blameable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\BlameableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\BlameableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Loggable\\Loggable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\LoggableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\SoftDeletableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableMethodsTrait' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\SoftDeletableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\Translatable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslatableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslatableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\Translation' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslationInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\TranslationInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Uuidable\\Uuidable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\UuidableInterface', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Uuidable\\UuidableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\UuidableInterface']);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # move interface to "Contract"
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Tree\\NodeInterface' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Contract\\Entity\\TreeNodeInterface',
        # suffix "Trait" for traits
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableMethodsTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableProperties' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameablePropertiesTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Blameable\\Blameable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Blameable\\BlameableTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodableMethodsTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodableProperties' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodablePropertiesTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Geocodable\\Geocodable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Geocodable\\GeocodableTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Loggable\\Loggable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Loggable\\LoggableTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableMethodsTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableProperties' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggablePropertiesTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Sluggable\\Sluggable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Sluggable\\SluggableTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableMethodsTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableProperties' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletablePropertiesTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\SoftDeletable\\SoftDeletableTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableMethodsTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableProperties' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampablePropertiesTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Timestampable\\Timestampable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Timestampable\\TimestampableTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableMethodsTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableProperties' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatablePropertiesTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\Translatable' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslatableTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationMethods' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationMethodsTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationProperties' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationPropertiesTrait',
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\Translation' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationTrait',
        # tree
        'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Tree\\Node' => 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Tree\\TreeNodeTrait',
    ]);
    $rectorConfig->ruleWithConfiguration(AddEntityIdByConditionRector::class, ['RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\Translation', 'RectorPrefix20220607\\Knp\\DoctrineBehaviors\\Model\\Translatable\\TranslationTrait']);
};
