<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Nette\Utils\Json;
use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Composer\Tests\Modifier\ComposerModifierTest
 */
final class ComposerModifier
{
    /** @var string */
    public const SECTION_REQUIRE = 'require';

    /** @var string */
    public const SECTION_REQUIRE_DEV = 'require-dev';

    /** @var string|null */
    private $filePath;

    /** @var string */
    private $command = 'composer update';

    /** @var ComposerModifierInterface[] */
    private $configuration = [];

    /**
     * @param ComposerModifierInterface[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, ComposerModifierInterface::class);
        $this->configuration = array_merge($this->configuration, $configuration);
    }

    /**
     * @param ComposerModifierInterface[] $configuration
     */
    public function reconfigure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, ComposerModifierInterface::class);
        $this->configuration = $configuration;
    }

    public function filePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath ?: getcwd() . '/composer.json';
    }

    public function command(string $command): void
    {
        $this->command = $command;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function modify(string $content): string
    {
        $composerData = Json::decode($content, Json::FORCE_ARRAY);
        foreach ($this->configuration as $composerChanger) {
            $composerData = $composerChanger->modify($composerData);
        }

        // TODO post process - if sort packages is set, we need sort them

        return Json::encode($composerData, Json::PRETTY);
    }
}
