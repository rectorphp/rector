<?php

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

### 11. CONFIGURE INPUT
$diffPath = 'https://github.com/symfony/symfony/commit/53a4711520d52bccd20fa6e616731114fa6eb61f.diff';
$classNamePrefix = 'Doctrine';



// @see https://regex101.com/r/tyABnc/1/
$beforeAfterPattern = '#^\-(?<before>[^-].*?)$\n\+(?<after>.*?)$#ms';

$diff = FileSystem::read($diffPath);
$beforeAfterMatches = Strings::matchAll($diff, $beforeAfterPattern);

$classesBeforeAfter = [];
foreach ($beforeAfterMatches as $beforeAfterMatch) {
    // file change
    if (Strings::contains($beforeAfterMatch['before'], '//')) {
        continue;
    }

    // @see https://regex101.com/r/tyABnc/2/
    $classNamePattern = sprintf('#(?<class_name>%s\\\\[\w|\\\\]+)#', $classNamePrefix);

    $classNameBefore = Strings::match($beforeAfterMatch['before'], $classNamePattern);
    $classNameAfter = Strings::match($beforeAfterMatch['after'], $classNamePattern);

    if ($classNameBefore === null || $classNameAfter === null) {
        continue;
    }

    $classNameBeforeValue = $classNameBefore['class_name'];
    $classNameAfterValue = $classNameAfter['class_name'];

    if (Strings::contains($classNameBeforeValue, 'Tests')) {
        continue;
    }

    // classes are the same, no change in the class name
    if ($classNameBeforeValue === $classNameAfterValue) {
        continue;
    }

    $classesBeforeAfter[$classNameBeforeValue] = $classNameAfterValue;
}

$classesBeforeAfter = array_unique($classesBeforeAfter);
ksort($classesBeforeAfter);

$yaml = Yaml::dump($classesBeforeAfter);
echo PHP_EOL;
echo $yaml;
echo PHP_EOL;
