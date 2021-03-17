<?php

declare(strict_types=1);

namespace Rector\Composer\Modifier;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;

final class ComposerModifier
{
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

    public function modify(ComposerJson $composerJson): void
    {
        foreach ($this->composerRectors as $composerRector) {
            $composerRector->refactor($composerJson);
        }
    }

    public function enabled(): bool
    {
        return $this->composerRectors !== [];
    }
}
