<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CoalesceCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (true) {
            $result = random_int(0, 1000);
            return $result ? NULL : 1;
        }

        $result = random_int(0, 1000);
        return $result ? 1 : null;
    }
}

?>
-----
<?php

namespace Rector\Symfony4\Tests\Rector\ClassMethod\ConsoleExecuteReturnIntRector\Fixture;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CoalesceCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (true) {
            $result = random_int(0, 1000);
            return $result ? 0 : 1;
        }

        $result = random_int(0, 1000);
        return $result ? 1 : 0;
    }
}

?>
