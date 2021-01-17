<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    /**
     * @var ChangedFilesDetector
     */
    protected $changedFilesDetector;

    /**
     * @required
     */
    public function autowireAbstractCommand(ChangedFilesDetector $changedFilesDetector): void
    {
        $this->changedFilesDetector = $changedFilesDetector;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $application = $this->getApplication();

        $optionDebug = $input->getOption(Option::OPTION_DEBUG);
        if ($optionDebug) {
            if ($application === null) {
                return;
            }

            $application->setCatchExceptions(false);

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
