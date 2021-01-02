<?php

declare(strict_types=1);

namespace Rector\Composer\Rector;

use Rector\Composer\ComposerModifier\ComposerModifierInterface;
use Webmozart\Assert\Assert;

final class ComposerRector
{
    public const SECTION_REQUIRE = 'require';

    public const SECTION_REQUIRE_DEV = 'require-dev';

    /** @var string|null */
    private $filePath;

    /** @var string */
    private $command = 'composer update';

    /** @var ComposerModifierInterface[] */
    private $configuration = [];

    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, ComposerModifierInterface::class);
        $this->configuration = array_merge($this->configuration, $configuration);
    }

    public function reconfigure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, ComposerModifierInterface::class);
        $this->configuration = $configuration;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath ?: getcwd() . '/composer.json';
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function refactor(string $content): string
    {
        $composerData = json_decode($content, true);
        foreach ($this->configuration as $composerChanger) {
            $composerData = $composerChanger->modify($composerData);
        }

        // TODO post process - if sort packages is set, we need sort them

        return json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
