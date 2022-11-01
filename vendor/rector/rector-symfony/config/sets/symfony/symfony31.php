<?php

declare (strict_types=1);
namespace RectorPrefix202211;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Symfony\\Component\\Yaml\\Yaml', 'parse', 1, [\false, \false, \true], 'Symfony\\Component\\Yaml\\Yaml::PARSE_OBJECT_FOR_MAP'), new ReplaceArgumentDefaultValue('Symfony\\Component\\Yaml\\Yaml', 'parse', 1, [\false, \true], 'Symfony\\Component\\Yaml\\Yaml::PARSE_OBJECT'), new ReplaceArgumentDefaultValue('Symfony\\Component\\Yaml\\Yaml', 'parse', 1, \true, 'Symfony\\Component\\Yaml\\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE'), new ReplaceArgumentDefaultValue('Symfony\\Component\\Yaml\\Yaml', 'parse', 1, \false, 0), new ReplaceArgumentDefaultValue('Symfony\\Component\\Yaml\\Yaml', 'dump', 3, [\false, \true], 'Symfony\\Component\\Yaml\\Yaml::DUMP_OBJECT'), new ReplaceArgumentDefaultValue('Symfony\\Component\\Yaml\\Yaml', 'dump', 3, \true, 'Symfony\\Component\\Yaml\\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE')]);
};
