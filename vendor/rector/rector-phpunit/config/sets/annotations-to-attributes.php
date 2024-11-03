<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\AnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\TicketAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DependsAnnotationWithValueToAttributeRector;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector;
use Rector\PHPUnit\ValueObject\AnnotationWithValueToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        TicketAnnotationToAttributeRector::class,
        TestWithAnnotationToAttributeRector::class,
        DataProviderAnnotationToAttributeRector::class,
        CoversAnnotationWithValueToAttributeRector::class,
        /**
         * Currently handle:
         *      - @depends Methodname
         *      - @depends Classname::class
         *      - @depends clone MethodName
         *
         * Todo:
         *      - @depends Class::MethodName
         *      - @depends !clone, shallowClone, !shallowClone
         */
        DependsAnnotationWithValueToAttributeRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(AnnotationWithValueToAttributeRector::class, [new AnnotationWithValueToAttribute('backupGlobals', 'PHPUnit\\Framework\\Attributes\\BackupGlobals', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('backupStaticAttributes', 'PHPUnit\\Framework\\Attributes\\BackupStaticProperties', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('preserveGlobalState', 'PHPUnit\\Framework\\Attributes\\PreserveGlobalState', ['enabled' => \true, 'disabled' => \false]), new AnnotationWithValueToAttribute('depends', 'PHPUnit\\Framework\\Attributes\\Depends'), new AnnotationWithValueToAttribute('group', 'PHPUnit\\Framework\\Attributes\\Group'), new AnnotationWithValueToAttribute('uses', 'PHPUnit\\Framework\\Attributes\\UsesClass'), new AnnotationWithValueToAttribute('testDox', 'PHPUnit\\Framework\\Attributes\\TestDox'), new AnnotationWithValueToAttribute('testdox', 'PHPUnit\\Framework\\Attributes\\TestDox')]);
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://github.com/sebastianbergmann/phpunit/issues/4502
        new AnnotationToAttribute('after', 'PHPUnit\\Framework\\Attributes\\After'),
        new AnnotationToAttribute('afterClass', 'PHPUnit\\Framework\\Attributes\\AfterClass'),
        new AnnotationToAttribute('before', 'PHPUnit\\Framework\\Attributes\\Before'),
        new AnnotationToAttribute('beforeClass', 'PHPUnit\\Framework\\Attributes\\BeforeClass'),
        // CodeCoverageIgnore attribute is deprecated.
        // @see https://github.com/rectorphp/rector-phpunit/pull/304
        // new AnnotationToAttribute('codeCoverageIgnore', 'PHPUnit\Framework\Attributes\CodeCoverageIgnore'),
        new AnnotationToAttribute('coversNothing', 'PHPUnit\\Framework\\Attributes\\CoversNothing'),
        new AnnotationToAttribute('doesNotPerformAssertions', 'PHPUnit\\Framework\\Attributes\\DoesNotPerformAssertions'),
        new AnnotationToAttribute('large', 'PHPUnit\\Framework\\Attributes\\Large'),
        new AnnotationToAttribute('medium', 'PHPUnit\\Framework\\Attributes\\Medium'),
        new AnnotationToAttribute('preCondition', 'PHPUnit\\Framework\\Attributes\\PostCondition'),
        new AnnotationToAttribute('postCondition', 'PHPUnit\\Framework\\Attributes\\PreCondition'),
        new AnnotationToAttribute('runInSeparateProcess', 'PHPUnit\\Framework\\Attributes\\RunInSeparateProcess'),
        new AnnotationToAttribute('runTestsInSeparateProcesses', 'PHPUnit\\Framework\\Attributes\\RunTestsInSeparateProcesses'),
        new AnnotationToAttribute('small', 'PHPUnit\\Framework\\Attributes\\Small'),
        new AnnotationToAttribute('test', 'PHPUnit\\Framework\\Attributes\\Test'),
    ]);
};
