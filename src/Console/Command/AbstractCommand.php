<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\Strings;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\ShellCode;

abstract class AbstractCommand extends Command
{
    /**
     * @var TextDescriptor
     */
    private $textDescriptor;

    /**
     * @required
     */
    public function setDescriptor(TextDescriptor $textDescriptor): void
    {
        $this->textDescriptor = $textDescriptor;
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        // show help on arguments fail
        try {
            return parent::run($input, $output);
        } catch (RuntimeException $throwable) {
            if (Strings::match($throwable->getMessage(), '#Not enough arguments#')) {
                // sometimes there is "command" argument, not really needed on fail of chosen command and missing argument
                $arguments = $this->getDefinition()->getArguments();
                if (isset($arguments['command'])) {
                    unset($arguments['command']);

                    $this->getDefinition()->setArguments($arguments);
                }

                $this->textDescriptor->describe($output, $this);

                return ShellCode::SUCCESS;
            }

            throw $throwable;
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('debug')) {
            $this->getApplication()->setCatchExceptions(false);
        }
    }
}
