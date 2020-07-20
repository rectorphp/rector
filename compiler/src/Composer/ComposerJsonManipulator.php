<?php

declare(strict_types=1);

namespace Rector\Compiler\Composer;

use Nette\Utils\FileSystem as NetteFileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

final class ComposerJsonManipulator
{
    /**
     * @var string[]
     */
    private const KEYS_TO_REMOVE = ['replace'];

    /**
     * @var string
     */
    private const REQUIRE = 'require';

    /**
     * @var string
     */
    private const PHPSTAN_PHPSTAN = 'phpstan/phpstan';

    /**
     * @var string
     */
    private const PHPSTAN_COMPOSER_JSON = 'https://raw.githubusercontent.com/phpstan/phpstan-src/%s/composer.json';

    /**
     * @var string
     */
    private $originalComposerJsonFileContent;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConsoleDiffer
     */
    private $consoleDiffer;

    public function __construct(Filesystem $filesystem, ConsoleDiffer $consoleDiffer)
    {
        $this->filesystem = $filesystem;
        $this->consoleDiffer = $consoleDiffer;
    }

    public function fixComposerJson(string $composerJsonFile): void
    {
        $fileContent = NetteFileSystem::read($composerJsonFile);
        $this->originalComposerJsonFileContent = $fileContent;

        $json = Json::decode($fileContent, Json::FORCE_ARRAY);
        $json = $this->removeDevKeys($json);
        $json = $this->replacePHPStanWithPHPStanSrc($json);

        $encodedJson = Json::encode($json, Json::PRETTY);

        // show diff
        if ($encodedJson !== $this->originalComposerJsonFileContent) {
            $this->consoleDiffer->diff($this->originalComposerJsonFileContent, $encodedJson);
        }

        $this->filesystem->dumpFile($composerJsonFile, $encodedJson);
    }

    /**
     * This prevent root composer.json constant override
     */
    public function restoreComposerJson(string $composerJsonFile): void
    {
        $this->filesystem->dumpFile($composerJsonFile, $this->originalComposerJsonFileContent);
    }

    private function removeDevKeys(array $json): array
    {
        foreach (self::KEYS_TO_REMOVE as $keyToRemove) {
            unset($json[$keyToRemove]);
        }
        return $json;
    }

    /**
     * Use phpstan/phpstan-src, because the phpstan.phar cannot be packed into rector.phar
     */
    private function replacePHPStanWithPHPStanSrc(array $json): array
    {
        // already replaced
        if (! isset($json[self::REQUIRE][self::PHPSTAN_PHPSTAN])) {
            return $json;
        }

        $phpstanVersion = $json[self::REQUIRE][self::PHPSTAN_PHPSTAN];
        // use exact version
        $phpstanVersion = ltrim($phpstanVersion, '^');

        $json[self::REQUIRE]['phpstan/phpstan-src'] = $phpstanVersion;
        unset($json[self::REQUIRE][self::PHPSTAN_PHPSTAN]);

        $json['repositories'][] = [
            'type' => 'vcs',
            'url' => 'https://github.com/phpstan/phpstan-src.git',
        ];

        return $this->addDevDependenciesFromPHPStan($json, $phpstanVersion);
    }

    private function addDevDependenciesFromPHPStan(array $json, string $phpstanVersion): array
    {
        // add dev dependencies from PHPStan composer.json
        $phpstanComposerJsonFilePath = sprintf(self::PHPSTAN_COMPOSER_JSON, $phpstanVersion);
        $phpstanComposerJson = $this->readRemoteFileToJson($phpstanComposerJsonFilePath);

        if (isset($phpstanComposerJson[self::REQUIRE])) {
            foreach ($phpstanComposerJson[self::REQUIRE] as $package => $version) {
                if (! Strings::startsWith($version, 'dev-master')) {
                    continue;
                }

                $json[self::REQUIRE][$package] = $version;
            }
        }

        return $json;
    }

    private function readRemoteFileToJson(string $jsonFilePath): array
    {
        $jsonFileContent = NetteFileSystem::read($jsonFilePath);

        return (array) Json::decode($jsonFileContent, Json::FORCE_ARRAY);
    }
}
