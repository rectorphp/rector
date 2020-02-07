<?php

use Symfony\Component\Yaml\Yaml;

function parseFileRector()
{
    $parsedFile = Yaml::parse('someFile.yml');
    $parsedFile = Yaml::parse('someFile.yaml');
    $parsedFile = Yaml::parse("someFile.yaml");
    $parsedFile = Yaml::parse($directory . "someFile.yaml");
    $parsedFile = Yaml::parse(__DIR__ . 'someFile.yaml');
}

?>
-----
<?php

use Symfony\Component\Yaml\Yaml;

function parseFileRector()
{
    $parsedFile = Yaml::parse(file_get_contents('someFile.yml'));
    $parsedFile = Yaml::parse(file_get_contents('someFile.yaml'));
    $parsedFile = Yaml::parse(file_get_contents("someFile.yaml"));
    $parsedFile = Yaml::parse(file_get_contents($directory . "someFile.yaml"));
    $parsedFile = Yaml::parse(file_get_contents(__DIR__ . 'someFile.yaml'));
}

?>
