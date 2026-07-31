<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\RequiresAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\TicketAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector;
use Rector\PHPUnit\ValueObject\AnnotationWithValueToAttribute;
/**
 * Annotation to attribute pairs bound to the PHPUnit version installed in the analysed project,
 * as not every attribute exists in every PHPUnit version.
 */
return static function (RectorConfig $rectorConfig): void {
    // each of these rules declares the "phpunit/phpunit" version its attributes were added in,
    // @see ComposerPackageConstraintInterface
    $rectorConfig->rules([TicketAnnotationToAttributeRector::class, TestWithAnnotationToAttributeRector::class, DataProviderAnnotationToAttributeRector::class, CoversAnnotationWithValueToAttributeRector::class, RequiresAnnotationWithValueToAttributeRector::class, DependsAnnotationWithValueToAttributeRector::class]);
    // both attributes were added in PHPUnit 10.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationWithValueToAttributeRector::class, [
        // the PHPUnit 10 spelling of the "backupStaticAttributes" annotation
        new AnnotationWithValueToAttribute('backupStaticProperties', 'PHPUnit\Framework\Attributes\BackupStaticProperties', ['enabled' => \true, 'disabled' => \false]),
        new AnnotationWithValueToAttribute('excludeGlobalVariableFromBackup', 'PHPUnit\Framework\Attributes\ExcludeGlobalVariableFromBackup'),
    ], 'phpunit/phpunit', '>=10.0');
    // the RunClassInSeparateProcess attribute was added in PHPUnit 10.0 and removed in PHPUnit 13.0
    $rectorConfig->ruleWithConfigurationComposerVersionBound(AnnotationToAttributeRector::class, [new AnnotationToAttribute('runClassInSeparateProcess', 'PHPUnit\Framework\Attributes\RunClassInSeparateProcess')], 'phpunit/phpunit', '>=10.0 <13.0');
};
