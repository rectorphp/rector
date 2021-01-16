<?php

declare(strict_types=1);

namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Composer\ValueObject\PackageAndVersion;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Composer\Tests\Rector\ChangePackageVersionRector\ChangePackageVersionRectorTest
 */
final class ChangePackageVersionRector implements ComposerRectorInterface
{
    /**
     * @var string
     */
    public const PACKAGES_AND_VERSIONS = 'packages_and_versions';

    /**
     * @var PackageAndVersion[]
     */
    private $packagesAndVersions = [];

    public function refactor(ComposerJson $composerJson): void
    {
        foreach ($this->packagesAndVersions as $packagesAndVersion) {
            $composerJson->changePackageVersion(
                $packagesAndVersion->getPackageName(),
                $packagesAndVersion->getVersion()
            );
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change package version `composer.json`', [new ConfiguredCodeSample(
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
        "symfony/console": "^4.4"
    }
}
CODE_SAMPLE
            , [
                self::PACKAGES_AND_VERSIONS => [new PackageAndVersion('symfony/console', '^4.4')],
            ]
        ),
        ]);
    }

    /**
     * @param array<string, PackageAndVersion[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->packagesAndVersions = $configuration[self::PACKAGES_AND_VERSIONS] ?? [];
    }
}
