<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Composer\Tests\Modifier\ComposerModifierTest
 */
final class ComposerModifier
{
    /**
     * @var string
     */
    private const COMPOSER_UPDATE = 'composer update';

    /**
     * @var string|null
     */
    private $filePath;

    /**
     * @var ComposerModifierInterface[]
     */
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

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath ?: getcwd() . '/composer.json';
    }

    public function getCommand(): string
    {
        return self::COMPOSER_UPDATE;
    }

    public function modify(ComposerJson $composerJson): void
    {
        foreach ($this->configuration as $composerChanger) {
            $composerChanger->modify($composerJson);
        }
    }
}
