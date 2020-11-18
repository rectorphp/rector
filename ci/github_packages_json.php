<?php

declare(strict_types=1);

// get top page count x10 packages names
$pageCount = 5;

$packagistPackageProvider = new PackagistPackageProvider();
$packagistPackageProvider->providePackageJsonListForPage($pageCount);

final class PackagistPackageProvider
{
    public function providePackageJsonListForPage(int $pageCount): void
    {
        $packageNames = [];

        for ($i = 1; $i <= $pageCount; ++$i) {
            $json = file_get_contents('https://packagist.org/explore/popular.json?page=' . $i);
            $array = json_decode($json, true);

            foreach ($array['packages'] as $package) {
                if ($this->isMetaPackage($package['name'])) {
                    continue;
                }


                $packageNames[] = $package['name'];
            }
        }

        echo json_encode(['package_name' => $packageNames]);
    }

    private function isMetaPackage(string $packageName): bool
    {
        // skip metapackages
        [$organization, $project] = explode('/', $packageName);
        if ($organization === 'psr') {
            return true;
        }

        if (strpos($project, 'polyfill') !== false) {
            return true;
        }

        return false;
    }
}
