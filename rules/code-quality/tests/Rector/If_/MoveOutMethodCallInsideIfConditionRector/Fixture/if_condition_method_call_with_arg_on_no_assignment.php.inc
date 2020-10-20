<?php

namespace Rector\Php\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption(Option::OPTION_DEBUG)) {
            return;
        }
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    public function run(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $inputGetOption = $input->getOption(Option::OPTION_DEBUG);
        if ($inputGetOption) {
            return;
        }
    }
}

?>
