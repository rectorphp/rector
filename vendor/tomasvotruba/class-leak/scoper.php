<?php

declare (strict_types=1);
namespace RectorPrefix202608;

require __DIR__ . '/vendor/autoload.php';
$nowDateTime = new \DateTime('now');
$timestamp = $nowDateTime->format('Ym');
// see https://github.com/humbug/php-scoper
return ['prefix' => 'ClassLeak' . $timestamp, 'expose-constants' => ['#^SYMFONY\_[\p{L}_]+$#'], 'exclude-namespaces' => ['#^TomasVotruba\\\\ClassLeak#', '#^Symfony\\\\Polyfill#'], 'exclude-files' => [
    // do not prefix "trigger_deprecation" from symfony - https://github.com/symfony/symfony/commit/0032b2a2893d3be592d4312b7b098fb9d71aca03
    // these paths are relative to this file location, so it should be in the root directory
    'vendor/symfony/deprecation-contracts/function.php',
], 'patchers' => [function (string $filePath, string $prefix, string $content): string {
    if (\substr_compare($filePath, 'src/Filtering/PossiblyUnusedClassesFilter.php', -\strlen('src/Filtering/PossiblyUnusedClassesFilter.php')) !== 0) {
        return $content;
    }
    $content = \preg_replace_callback('#DEFAULT_TYPES_TO_SKIP = (?<content>.*?)\;#ms', function (array $match) use ($prefix) {
        $unprefixedValue = \preg_replace('#\'' . $prefix . '\\\\#', '\'', $match['content']);
        return 'DEFAULT_TYPES_TO_SKIP = ' . $unprefixedValue . ';';
    }, $content);
    return \preg_replace_callback('#DEFAULT_ATTRIBUTES_TO_SKIP = (?<content>.*?)\;#ms', function (array $match) use ($prefix) {
        $unprefixedValue = \preg_replace('#\'' . $prefix . '\\\\#', '\'', $match['content']);
        return 'DEFAULT_ATTRIBUTES_TO_SKIP = ' . $unprefixedValue . ';';
    }, $content);
}]];
