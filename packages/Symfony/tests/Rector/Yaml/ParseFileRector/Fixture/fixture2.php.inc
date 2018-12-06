<?php

use Symfony\Component\Yaml\Yaml;

function parseFileRector2()
{
    $someFile = __DIR__ . '/someFile.yaml';
    $parsedFile = Yaml::parse($someFile);
}

?>
-----
<?php

use Symfony\Component\Yaml\Yaml;

function parseFileRector2()
{
    $someFile = __DIR__ . '/someFile.yaml';
    $parsedFile = Yaml::parse(file_get_contents($someFile));
}

?>
