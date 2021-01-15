<?php

declare(strict_types=1);

namespace Rector\Composer\Rector;

use Rector\Composer\Contract\Rector\ComposerRectorInterface;
use Rector\Composer\ValueObject\ComposerModifier\AddPackageToRequire;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddPackageToRequireRector implements ComposerRectorInterface
{
    /**
     * @var string
     */
    public const ADD_PACKAGES_TO_REQUIRE = 'add_packages_to_require';

    /**
     * @var AddPackageToRequire[]
     */
    private $addPackagesToRequire = [];

    public function refactor(ComposerJson $composerJson): void
    {
        foreach ($this->addPackagesToRequire as $addPackageToRequire) {
            $composerJson->addRequiredPackage(
                $addPackageToRequire->getPackageName(),
                $addPackageToRequire->getVersion()
            );
        }
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add package to "require" in `composer.json`', [new ConfiguredCodeSample(
            <<<'CODE_SAMPLE'
{
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
                self::ADD_PACKAGES_TO_REQUIRE => [new AddPackageToRequire('symfony/console', '^3.4')],
            ]
        ),
        ]);
    }

    /**
     * @param array<string, AddPackageToRequire[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $this->addPackagesToRequire = $configuration[self::ADD_PACKAGES_TO_REQUIRE] ?? [];
    }
}
