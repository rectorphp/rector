<?php

namespace Rector\Symfony4\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Symfony\Component\Process\Process;

function stringToArgumentArray3()
{
    $source = '/src';

    $command = sprintf(
        '%s check %s --config %s --fix',
        ECS_BIN_PATH,
        implode(' ', $source),
        ECS_AFTER_RECTOR_CONFIG
    );

    $process = new Process($command);
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Symfony\Component\Process\Process;

function stringToArgumentArray3()
{
    $source = '/src';

    $command = [ECS_BIN_PATH, 'check', implode(' ', $source), '--config', ECS_AFTER_RECTOR_CONFIG, '--fix'];

    $process = new Process($command);
}

?>
