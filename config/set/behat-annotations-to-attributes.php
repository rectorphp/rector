<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Given', 'Behat\\Step\\Given', [], \true), new AnnotationToAttribute('When', 'Behat\\Step\\When', [], \true), new AnnotationToAttribute('Then', 'Behat\\Step\\Then', [], \true), new AnnotationToAttribute('BeforeSuite', 'Behat\\Hook\\BeforeSuite', [], \true), new AnnotationToAttribute('AfterSuite', 'Behat\\Hook\\AfterSuite', [], \true), new AnnotationToAttribute('BeforeFeature', 'Behat\\Hook\\BeforeFeature', [], \true), new AnnotationToAttribute('AfterFeature', 'Behat\\Hook\\AfterFeature', [], \true), new AnnotationToAttribute('BeforeScenario', 'Behat\\Hook\\BeforeScenario', [], \true), new AnnotationToAttribute('AfterScenario', 'Behat\\Hook\\AfterScenario', [], \true), new AnnotationToAttribute('BeforeStep', 'Behat\\Hook\\BeforeStep', [], \true), new AnnotationToAttribute('AfterStep', 'Behat\\Hook\\AfterStep', [], \true)]);
};
