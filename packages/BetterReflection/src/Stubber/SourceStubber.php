<?php declare(strict_types=1);

namespace Rector\BetterReflection\Stubber;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Inspired by https://github.com/Roave/BetterReflection/blob/master/src/SourceLocator/Reflection/SourceStubber.php
 */
final class SourceStubber
{
    /**
     * @var string
     */
    private $stubDirectory;

    /**
     * @var SplFileInfo[]
     */
    private $stubs = [];

    public function __construct()
    {
        $this->stubDirectory = __DIR__ . '/../../stub';
    }

    public function getStubFileInfoForName(string $name): ?SplFileInfo
    {
        $this->loadStubs();

        if (! isset($this->stubs[$name])) {
            return null;
        }

        return $this->stubs[$name];
    }

    private function loadStubs(): void
    {
        if (count($this->stubs)) {
            return;
        }

        $finder = Finder::create()
            ->files()
            ->in($this->stubDirectory);

        foreach ($finder->getIterator() as $fileInfo) {
            $class = $this->fileNameToClass($fileInfo);
            $this->stubs[$class] = $fileInfo;
        }
    }

    private function fileNameToClass(SplFileInfo $fileInfo): string
    {
        return str_replace('.', '\\', $fileInfo->getBasename('.stub'));
    }
}
