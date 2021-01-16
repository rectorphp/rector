<?php

declare(strict_types=1);

namespace Rector\Composer;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

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
