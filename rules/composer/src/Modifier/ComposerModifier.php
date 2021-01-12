<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierConfigurationInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Composer\Tests\Modifier\ComposerModifierTest
 */
final class ComposerModifier
{
    /** @var ComposerModifierFactory */
    private $composerModifierFactory;

    /** @var string|null */
    private $filePath;

    /** @var string */
    private $command = 'composer update';

    /** @var ComposerModifierConfigurationInterface[] */
    private $configuration = [];

    public function __construct(ComposerModifierFactory $composerModifierFactory)
    {
        $this->composerModifierFactory = $composerModifierFactory;
    }

    /**
     * @param ComposerModifierConfigurationInterface[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, ComposerModifierConfigurationInterface::class);
        $this->configuration = array_merge($this->configuration, $configuration);
    }

    /**
     * @param ComposerModifierConfigurationInterface[] $configuration
     */
    public function reconfigure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, ComposerModifierConfigurationInterface::class);
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

    public function modify(ComposerJson $composerJson): ComposerJson
    {
        foreach ($this->configuration as $composerModifierConfiguration) {
            $composerModifier = $this->composerModifierFactory->create($composerModifierConfiguration);
            if ($composerModifier === null) {
                // TODO add some error message to output
                continue;
            }
            $composerJson = $composerModifier->modify($composerJson, $composerModifierConfiguration);
        }

        return $composerJson;
    }
}
