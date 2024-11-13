<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
return RectorConfig::configure()->withSets([__DIR__ . '/twig-underscore-to-namespace.php']);
