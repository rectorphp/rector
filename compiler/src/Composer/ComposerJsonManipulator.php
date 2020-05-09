<?php

declare(strict_types=1);

namespace Rector\Compiler\Composer;

use Nette\Utils\FileSystem as NetteFileSystem;
use Nette\Utils\Json;
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
        $json = $this->addReplace($json);

        // see https://github.com/phpstan/phpstan-src/blob/769669d4ec2a4839cb1aa25a3a29f05aa86b83ed/composer.json#L19
        $json = $this->addAllowDevPackages($json);

        $encodedJson = Json::encode($json, Json::PRETTY);

        // show diff
        $this->consoleDiffer->diff($this->originalComposerJsonFileContent, $encodedJson);

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
        $json[self::REQUIRE]['phpstan/phpstan-src'] = $phpstanVersion;
        unset($json[self::REQUIRE][self::PHPSTAN_PHPSTAN]);

        $json['repositories'][] = [
            'type' => 'vcs',
            'url' => 'https://github.com/phpstan/phpstan-src.git',
        ];

        return $json;
    }

    /**
     * This prevent installing packages, that are not needed here.
     */
    private function addReplace(array $json): array
    {
        $json['replace'] = [
            'symfony/var-dumper' => '*',
        ];

        return $json;
    }

    private function addAllowDevPackages(array $json): array
    {
        $json['minimum-stability'] = 'dev';
        $json['prefer-stable'] = true;

        return $json;
    }
}
