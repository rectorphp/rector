<?php declare(strict_types=1);

use Nette\Utils\Strings;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/../vendor/autoload.php';

$finder = (new Finder())->name('*.yaml')
    ->in(__DIR__ . '/../config')
    ->files();

/** @var SplFileInfo $splFileInfo */
foreach ($finder as $splFileInfo) {
    $yamlContent = Yaml::parseFile($splFileInfo->getRealPath());
    if (! isset($yamlContent['services'])) {
        continue;
    }

    foreach (array_keys($yamlContent['services']) as $service) {
        // configuration → skip
        if (Strings::startsWith($service, '_')) {
            continue;
        }

        // autodiscovery → skip
        if (Strings::endsWith($service, '\\')) {
            continue;
        }

        if (ClassExistenceStaticHelper::doesClassLikeExist($service)) {
            continue;
        }

        throw new ShouldNotHappenException(sprintf(
            'Service "%s" from config "%s" was not found. Check if it really exists or is even autoload, please',
            $service,
            $splFileInfo->getRealPath()
        ));
    }
}

echo 'All configs have existing services - good job!' . PHP_EOL;
