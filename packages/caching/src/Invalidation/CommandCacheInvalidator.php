<?php

declare(strict_types=1);

namespace Rector\Caching\Invalidation;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Configuration\Option;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

final class CommandCacheInvalidator
{
    /**
     * @var ChangedFilesDetector
     */
    private $changedFilesDetector;

    /**
     * @var Application
     */
    private $application;

    public function __construct(ChangedFilesDetector $changedFilesDetector)
    {
        $this->changedFilesDetector = $changedFilesDetector;
    }

    /**
     * To avoid circular reference
     * @required
     */
    public function autowireCommandCacheInvalidator(Application $application): void
    {
        $this->application = $application;
    }

    public function invalidateIfInput(InputInterface $input): void
    {
        $optionDebug = (bool) $input->getOption(Option::OPTION_DEBUG);
        if ($optionDebug) {
            $this->application->setCatchExceptions(false);

            // clear cache
            $this->changedFilesDetector->clear();
            return;
        }

        // clear cache
        $optionClearCache = (bool) $input->getOption(Option::OPTION_CLEAR_CACHE);
        if ($optionClearCache) {
            $this->changedFilesDetector->clear();
        }
    }
}
