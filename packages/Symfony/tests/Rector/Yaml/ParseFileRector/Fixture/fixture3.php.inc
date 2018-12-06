<?php

use Symfony\Component\Yaml\Yaml;

function parseFileRector3($someFile) {
    return Yaml::parse($someFile);
}

?>
-----
<?php

use Symfony\Component\Yaml\Yaml;

function parseFileRector3($someFile) {
    return Yaml::parse(file_get_contents($someFile));
}

?>
