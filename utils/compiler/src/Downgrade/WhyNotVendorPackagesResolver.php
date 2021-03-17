<?php

declare(strict_types=1);

namespace Rector\Compiler\Downgrade;

use Nette\Utils\Strings;
use Symfony\Component\Process\Process;

final class WhyNotVendorPackagesResolver
{
    /**
     * @var string
     * @see https://regex101.com/r/Q1Ty4R/1
     */
    private const PACKAGE_NAME_TO_PACKAGE_PATH_REGEX = '#^(?<package_name>[\w\-]+\/[\w\-]+)\s+(.*?)(?<package_path>vendor.*?)$#ms';

    /**
     * @var string
     * @see https://regex101.com/r/npmxZ7/1
     */
    private const PACKAGE_NAME_REGEX = '#^(?<package_name>[\w\-]+\/[\w\-]+)#ms';

    /**
     * @return string[]
     */
    public function resolveFromPhpVersion(string $targetPhpVersion): array
    {
        $packageNames = $this->resolveWhyNotPackageNames($targetPhpVersion);
        $packageNameToPath = $this->resolvePackageNamesToPaths();

        $packageNamesAsKeys = array_flip($packageNames);
        $matchingNamesToPaths = array_intersect_key($packageNameToPath, $packageNamesAsKeys);

        return array_values($matchingNamesToPaths);
    }

    /**
     * @return string[]
     */
    private function resolveWhyNotPackageNames(string $targetPhpVersion): array
    {
        $commandOutput = $this->runCommandToOutput(['composer', 'why-not', 'php', $targetPhpVersion]);

        $matches = Strings::matchAll($commandOutput, self::PACKAGE_NAME_REGEX);

        $packageNames = [];
        foreach ($matches as $match) {
            $packageNames[] = $match['package_name'];
        }

        return $packageNames;
    }

    /**
     * @return array<string, string>
     */
    private function resolvePackageNamesToPaths(): array
    {
        $commandOutput = $this->runCommandToOutput(['composer', 'info', '--path']);

        $matches = Strings::matchAll($commandOutput, self::PACKAGE_NAME_TO_PACKAGE_PATH_REGEX);

        $packageNameToPath = [];
        foreach ($matches as $match) {
            $packageName = (string) $match['package_name'];
            $packagePath = (string) $match['package_path'];
            $packageNameToPath[$packageName] = $packagePath;
        }

        return $packageNameToPath;
    }

    /**
     * @param string[] $commandLine
     */
    private function runCommandToOutput(array $commandLine): string
    {
        $process = new Process($commandLine);
        $process->run();
        return $process->getOutput();
    }
}
