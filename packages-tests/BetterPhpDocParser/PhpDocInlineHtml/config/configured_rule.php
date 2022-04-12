<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Tests\BetterPhpDocParser\PhpDocInlineHtml\Source\InlineHtmlRector;

return static function (RectorConfig $rectorConfig): void {
    $services = $rectorConfig->services();
    $services->set(InlineHtmlRector::class);
};
