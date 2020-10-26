<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Nette\Utils\Strings;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\ShellCode;

abstract class AbstractCommand extends Command
{
    /**
     * @var ChangedFilesDetector
     */
    protected $changedFilesDetector;

    /**
     * @var TextDescriptor
     */
    private $textDescriptor;

    /**
     * @required
     */
    public function autowireAbstractCommand(
        TextDescriptor $textDescriptor,
        ChangedFilesDetector $changedFilesDetector
    ): void {
        $this->textDescriptor = $textDescriptor;
        $this->changedFilesDetector = $changedFilesDetector;
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        // show help on arguments fail
        try {
            return parent::run($input, $output);
        } catch (RuntimeException $runtimeException) {
            if (Strings::contains($runtimeException->getMessage(), 'Not enough arguments')) {
                // sometimes there is "command" argument, not really needed on fail of chosen command and missing argument
                $arguments = $this->getDefinition()
                    ->getArguments();
                if (isset($arguments['command'])) {
                    unset($arguments['command']);

                    $this->getDefinition()
                        ->setArguments($arguments);
                }

                $this->textDescriptor->describe($output, $this);

                return ShellCode::SUCCESS;
            }

            throw $runtimeException;
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $optionDebug = $input->getOption(Option::OPTION_DEBUG);
        if ($optionDebug) {
            if ($this->getApplication() === null) {
                return;
            }

            $this->getApplication()
                ->setCatchExceptions(false);

            // clear cache
            $this->changedFilesDetector->clear();
        }
        $optionClearCache = $input->getOption(Option::OPTION_CLEAR_CACHE);

        // clear cache
        if ($optionClearCache) {
            $this->changedFilesDetector->clear();
        }
    }
}
