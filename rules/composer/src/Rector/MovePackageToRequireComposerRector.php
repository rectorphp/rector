<?php

declare(strict_types=1);

namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Composer\Tests\Rector\MovePackageToRequireComposerRector\MovePackageToRequireComposerRectorTest
 */
final class MovePackageToRequireComposerRector implements ComposerRectorInterface
{
    /**
     * @var string
     */
    public const PACKAGE_NAMES = 'package_names';

    /**
     * @var string[]
     */
    private $packageNames = [];

    public function refactor(ComposerJson $composerJson): void
    {
        foreach ($this->packageNames as $packageName) {
            $composerJson->movePackageToRequire($packageName);
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Moves package from "require-dev" to "require" in `composer.json`', [new ConfiguredCodeSample(
            <<<'CODE_SAMPLE'
{
    "require-dev": {
        "symfony/console": "^3.4"
    }
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
{
    "require": {
        "symfony/console": "^3.4"
    }
}
CODE_SAMPLE
            , [
                self::PACKAGE_NAMES => ['symfony/console'],
            ]
        ),
        ]);
    }

    /**
     * @param array<string, string[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->packageNames = $configuration[self::PACKAGE_NAMES] ?? [];
    }
}
