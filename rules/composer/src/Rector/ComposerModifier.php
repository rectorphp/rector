<?php

declare(strict_types=1);

namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
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
    private $composerJsonFilePath;

    /**
     * @var ComposerRectorInterface[]
     */
    private $composerRectors = [];

    /**
     * @param ComposerRectorInterface[] $composerRectors
     */
    public function __construct(array $composerRectors)
    {
        $this->composerRectors = $composerRectors;
    }

    public function setFilePath(string $filePath): void
    {
        $this->composerJsonFilePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->composerJsonFilePath ?: getcwd() . '/composer.json';
    }

    public function getCommand(): string
    {
        return self::COMPOSER_UPDATE;
    }

    public function modify(ComposerJson $composerJson): void
    {
        foreach ($this->composerRectors as $composerChanger) {
            $composerChanger->refactor($composerJson);
        }
    }
}
