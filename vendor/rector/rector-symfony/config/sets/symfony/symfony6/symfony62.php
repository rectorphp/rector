<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    // change to attribute before rename
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    // @see https://github.com/rectorphp/rector-symfony/issues/535#issuecomment-1783983383
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://github.com/symfony/symfony/pull/46907
        new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted'),
        // @see https://github.com/symfony/symfony/pull/46880
        new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache'),
        // @see https://github.com/symfony/symfony/pull/46906
        new AnnotationToAttribute('Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template'),
    ]);
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-security-core.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-security-http.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-mime.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-http-kernel.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-framework-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-twig-bridge.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-translation.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-doctrine-bridge.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-messenger.php');
    $rectorConfig->import(__DIR__ . '/symfony62/symfony62-mail-pace-mailer.php');
};
