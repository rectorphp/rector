<?php

declare(strict_types=1);

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Core\Exception\ShouldNotHappenException;
use Symfony\Component\Console\Command\Command;

require __DIR__ . '/../vendor/autoload.php';

$packageVersionResolver = new PackageVersionResolver();

$localPHPStanVersion = $packageVersionResolver->resolve(__DIR__ . '/../composer.json', 'phpstan/phpstan');
$downgradedPHPStanVersion = $packageVersionResolver->resolve(
    __DIR__ . '/../build/target-repository/composer.json',
    'phpstan/phpstan'
);

if ($localPHPStanVersion === $downgradedPHPStanVersion) {
    echo '[OK] PHPStan version in local and downgraded composer.json are equal, good job!' . PHP_EOL;
    exit(Command::SUCCESS);
}

echo sprintf(
    '[ERROR] PHPStan version in local composer.json is "%s", in downgraded "%s".%sMake sure they are equal first.',
    $localPHPStanVersion,
    $downgradedPHPStanVersion,
    PHP_EOL
) . PHP_EOL;
exit(Command::FAILURE);

final class PackageVersionResolver
{
    public function __construct(
        private readonly JsonFileReader $jsonFileReader = new JsonFileReader(),
    ) {
    }

    public function resolve(string $composerFilePath, string $packageName): string
    {
        $composerJson = $this->jsonFileReader->readFilePath($composerFilePath);
        $packageVersion = $composerJson['require'][$packageName] ?? null;

        if ($packageVersion === null) {
            throw new ShouldNotHappenException();
        }

        return $packageVersion;
    }
}


final class JsonFileReader
{
    /**
     * @return array<string, mixed>
     */
    public function readFilePath(string $filePath): array
    {
        $fileContents = FileSystem::read($filePath);
        return Json::decode($fileContents, Json::FORCE_ARRAY);
    }
}
