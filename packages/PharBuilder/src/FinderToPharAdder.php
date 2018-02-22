<?php declare(strict_types=1);

namespace Rector\PharBuilder;

use Phar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

final class FinderToPharAdder
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function addFinderToPhar(Finder $finder, Phar $phar): void
    {
        foreach ($finder as $fileInfo) {
            if ($this->symfonyStyle->isVerbose()) {
                $this->symfonyStyle->note(sprintf('Adding "%s" file', $fileInfo->getRelativePathname()));
            } else {
                $this->symfonyStyle->progressAdvance();
            }

            $phar->addFromString($fileInfo->getRelativePathname(), $fileInfo->getContents());
        }
    }
}
