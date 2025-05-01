<?php

declare (strict_types=1);
namespace RectorPrefix202505;

use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony73\Rector\Class_\CommandHelpToAttributeRector;
use Rector\Symfony\Symfony73\Rector\Class_\InvokableCommandRector;
// @see https://github.com/symfony/symfony/blame/7.3/UPGRADE-7.3.md
return RectorConfig::configure()->withRules([CommandHelpToAttributeRector::class, InvokableCommandRector::class]);
