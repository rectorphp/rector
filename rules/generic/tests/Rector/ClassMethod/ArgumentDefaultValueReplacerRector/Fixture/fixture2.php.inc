<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector\Fixture;

use Symfony\Component\Yaml\Yaml;

function argumentDefaultValue2()
{
    Yaml::parse('...', true);
    Yaml::parse('...', false);
    Yaml::parse('...', false, true);
    Yaml::parse('...', false, false, true);
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassMethod\ArgumentDefaultValueReplacerRector\Fixture;

use Symfony\Component\Yaml\Yaml;

function argumentDefaultValue2()
{
    Yaml::parse('...', \Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
    Yaml::parse('...', 0);
    Yaml::parse('...', \Symfony\Component\Yaml\Yaml::PARSE_OBJECT);
    Yaml::parse('...', \Symfony\Component\Yaml\Yaml::PARSE_OBJECT_FOR_MAP);
}

?>
