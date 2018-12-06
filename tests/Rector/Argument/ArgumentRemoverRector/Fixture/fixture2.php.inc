<?php

use Symfony\Component\Yaml\Yaml;

function argumentRemover()
{
    $yaml = [];

    Symfony\Component\Yaml\Yaml::parse($yaml, Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS);

    Symfony\Component\Yaml\Yaml::parse($yaml, Yaml::PARSE_KEYS_AS_STRINGS);

    Symfony\Component\Yaml\Yaml::parse($yaml, Symfony\Component\Yaml\Yaml::PARSE_CONSTANT);

    Yaml::parse($yaml, Yaml::PARSE_KEYS_AS_STRINGS);

    Symfony\Component\Yaml\Yaml::parse($yaml, 55);
    Symfony\Component\Yaml\Yaml::parse($yaml, 56);
    Symfony\Component\Yaml\Yaml::parse($yaml, 'hey');
    Symfony\Component\Yaml\Yaml::parse($yaml, 'hi');
    Symfony\Component\Yaml\Yaml::parse($yaml, 5.5);
}

?>
-----
<?php

use Symfony\Component\Yaml\Yaml;

function argumentRemover()
{
    $yaml = [];

    Symfony\Component\Yaml\Yaml::parse($yaml);

    Symfony\Component\Yaml\Yaml::parse($yaml);

    Symfony\Component\Yaml\Yaml::parse($yaml, Symfony\Component\Yaml\Yaml::PARSE_CONSTANT);

    Yaml::parse($yaml);

    Symfony\Component\Yaml\Yaml::parse($yaml);
    Symfony\Component\Yaml\Yaml::parse($yaml, 56);
    Symfony\Component\Yaml\Yaml::parse($yaml);
    Symfony\Component\Yaml\Yaml::parse($yaml, 'hi');
    Symfony\Component\Yaml\Yaml::parse($yaml);
}

?>
