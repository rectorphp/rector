<?php

declare(strict_types=1);

namespace Rector\Compiler\Composer;

use Nette\Utils\Json;
use Nette\Utils\Strings;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;
use Symplify\SmartFileSystem\SmartFileSystem;

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
    private const REQUIRE_DEV = 'require-dev';

    /**
     * @var string
     */
    private $originalComposerJsonFileContent;

    /**
     * @var ConsoleDiffer
     */
    private $consoleDiffer;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(ConsoleDiffer $consoleDiffer, SmartFileSystem $smartFileSystem)
    {
        $this->consoleDiffer = $consoleDiffer;
        $this->smartFileSystem = $smartFileSystem;
    }

    public function fixComposerJson(string $composerJsonFile): void
    {
        $fileContent = $this->smartFileSystem->readFile($composerJsonFile);
        $this->originalComposerJsonFileContent = $fileContent;

        $json = Json::decode($fileContent, Json::FORCE_ARRAY);
        $json = $this->removeDevKeys($json);
        $json = $this->replacePHPStanWithPHPStanSrc($json);

        $encodedJson = Json::encode($json, Json::PRETTY);

        // show diff
        if ($encodedJson !== $this->originalComposerJsonFileContent) {
            $this->consoleDiffer->diff($this->originalComposerJsonFileContent, $encodedJson);
        }

        $this->smartFileSystem->dumpFile($composerJsonFile, $encodedJson);
    }

    /**
     * This prevent root composer.json constant override
     */
    public function restoreComposerJson(string $composerJsonFile): void
    {
        $this->smartFileSystem->dumpFile($composerJsonFile, $this->originalComposerJsonFileContent);
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

        // temporary before phpstan/phpstan becomes tagged - fixes https://travis-ci.com/github/rectorphp/rector/builds/182040376
        $json[self::REQUIRE]['nikic/php-parser'] = '^4.9.1';

        $phpstanVersion = $json[self::REQUIRE][self::PHPSTAN_PHPSTAN];
        $phpstanVersion = ltrim($phpstanVersion, '^');
        // use dev-master till this get's to tagged: https://github.com/phpstan/phpstan-src/commit/535c0e25429c1e3dd0dd05f61b43a34830da2a09
        $json[self::REQUIRE]['phpstan/phpstan-src'] = 'dev-master';
        unset($json[self::REQUIRE][self::PHPSTAN_PHPSTAN]);

        // remove conflicting dev deps
        unset($json[self::REQUIRE_DEV]['slam/phpstan-extensions']);

        $json['repositories'][] = [
            'type' => 'vcs',
            'url' => 'https://github.com/phpstan/phpstan-src.git',
        ];

        $json = $this->addDevDependenciesFromPHPStan($json, $phpstanVersion);

        return $this->allowDevDependnecies($json);
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

    private function allowDevDependnecies(array $json): array
    {
        $json['minimum-stability'] = 'dev';
        $json['prefer-stable'] = true;

        return $json;
    }

    private function readRemoteFileToJson(string $jsonFilePath): array
    {
        $jsonFileContent = $this->smartFileSystem->readFile($jsonFilePath);

        return (array) Json::decode($jsonFileContent, Json::FORCE_ARRAY);
    }
}
