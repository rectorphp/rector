<?php declare(strict_types=1);

namespace Rector\Configuration;

use SplFileInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Finder\Finder;

final class RectorConfigFilePathHelper
{
    /**
     * @var string
     */
    private const LEVEL_OPTION_NAME = '--level';

    /**
     * @var string
     */
    private $levelDirectory = __DIR__ . '/../config/level';

    public function resolveLevel(InputInterface $input): ?string
    {
        if (! $input->hasParameterOption(self::LEVEL_OPTION_NAME)) {
            return null;
        }

        $levelConfigName = $input->getParameterOption(self::LEVEL_OPTION_NAME);

        $finder = Finder::create()
            ->files()
            ->name($levelConfigName . '.yml')
            ->in($this->levelDirectory);

        $firstFile = self::getFirstFileFromFinder($finder);
        if (! $firstFile) {
            return null;
        }

        return $firstFile->getRealPath();
    }

    private function getFirstFileFromFinder(Finder $finder): ?SplFileInfo
    {
        $iterator = $finder->getIterator();
        $iterator->rewind();

        return $iterator->current();
    }
}
