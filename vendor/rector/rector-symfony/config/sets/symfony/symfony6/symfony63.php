<?php

declare (strict_types=1);
namespace RectorPrefix202506;

use Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/6.3/UPGRADE-6.3.md
// @see \Rector\Symfony\Tests\Set\Symfony63\Symfony63Test
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony63/symfony63-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony63/symfony63-http-client.php');
    $rectorConfig->import(__DIR__ . '/symfony63/symfony63-messenger.php');
    $rectorConfig->import(__DIR__ . '/symfony63/symfony63-console.php');
};
