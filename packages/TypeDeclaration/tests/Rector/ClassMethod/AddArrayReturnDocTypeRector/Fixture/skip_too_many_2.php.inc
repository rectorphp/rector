<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Nette\Utils\Strings;

final class SkipToOMany2
{
    /**
     * @param string[] $packageNames
     */
    public function createPackagesData(array $packageNames): array
    {
        $packagesData = [];

        foreach ($packageNames as $packageName) {
            $packageKey = $this->createPackageKey($packageName);

            $packageDownloads = $this->provideForPackage($packageName);
            if ($packageDownloads === []) {
                continue;
            }

            // complete relative number of downloads
            $totalDownloads = array_sum($packageDownloads[1]);

            foreach ($packageDownloads[1] as $version => $absoluteDownloads) {
                $relativeRate = 100 * ($absoluteDownloads / $totalDownloads);

                $packageDownloads[1][$version] = [
                    'absolute_downloads' => $absoluteDownloads,
                    'relative_downloads' => round($relativeRate, 1),
                    'version_publish_date' => $this->provideForPackageAndVersion(
                        $packageName,
                        $version
                    ),
                ];
            }

            $packagesData[$packageKey] = [
                    'package_name' => $packageName,
                    'package_short_name' => Strings::after($packageName, '/'),
                ] + $packageDownloads;
        }

        return $packagesData;
    }

    private function createPackageKey(string $packageName): string
    {
        return Strings::replace($packageName, '#(/|-)#', '_');
    }

    /**
     * @return int[][]
     */
    private function provideForPackage(string $packageName): array
    {
        return [[1, 3]];
    }

    private function provideForPackageAndVersion(): ?string
    {
        return '' ? null : 'string';
    }
}
