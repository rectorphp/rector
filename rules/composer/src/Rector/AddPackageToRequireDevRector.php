<?php

declare(strict_types=1);

namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Composer\ValueObject\ComposerModifier\PackageAndVersion;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddPackageToRequireDevRector implements ComposerRectorInterface
{
    /**
     * @var string
     */
    public const PACKAGES_AND_VERSIONS = 'packages_and_version';

    /**
     * @var PackageAndVersion[]
     */
    private $packageAndVersions = [];

    public function refactor(ComposerJson $composerJson): void
    {
        foreach ($this->packageAndVersions as $packageAndVersion) {
            $composerJson->addRequiredDevPackage(
                $packageAndVersion->getPackageName(),
                $packageAndVersion->getVersion()
            );
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add package to "require-dev" in `composer.json`', [new ConfiguredCodeSample(
            <<<'CODE_SAMPLE'
{
}
CODE_SAMPLE
            ,
            <<<'CODE_SAMPLE'
{
    "require-dev": {
        "symfony/console": "^3.4"
    }
}
CODE_SAMPLE
            , [
                self::PACKAGES_AND_VERSIONS => [new PackageAndVersion('symfony/console', '^3.4')],
            ]
        ),
        ]);
    }

    /**
     * @param array<string, PackageAndVersion[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->packageAndVersions = $configuration[self::PACKAGES_AND_VERSIONS] ?? [];
    }
}
