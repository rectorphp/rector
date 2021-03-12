<?php

namespace Rector\Symfony4\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Symfony\Component\Process\Process;

function stringToArgumentArray()
{
    $process = new Process('ls -l');
    $process = new Process('ls  -l');

    $commitHash = 'abc';
    $process = new Process('sleep ' . (5 / 1000));
    $process = new Process('git describe --contains ' . $commitHash);
    $process = new Process($commitHash . 'git describe --contains');
    $process = new Process($commitHash . 'git describe --contains' . $commitHash);

    $process = new Process(['ls', '-l']);

    $value = 123;
    $process = new Process($value);
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Symfony\Component\Process\Process;

function stringToArgumentArray()
{
    $process = new Process(['ls', '-l']);
    $process = new Process(['ls', '-l']);

    $commitHash = 'abc';
    $process = new Process(['sleep', 5 / 1000]);
    $process = new Process(['git', 'describe', '--contains', $commitHash]);
    $process = new Process([$commitHash, 'git', 'describe', '--contains']);
    $process = new Process([$commitHash, 'git', 'describe', '--contains', $commitHash]);

    $process = new Process(['ls', '-l']);

    $value = 123;
    $process = new Process($value);
}

?>
