<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Contract\ComposerModifier\ComposerModifierInterface;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Webmozart\Assert\Assert;

/**
 * Replace one package for another
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\ReplacePackageTest
 */
final class ReplacePackage implements ComposerModifierInterface
{
    /**
     * @var string
     */
    private $oldPackageName;

    /**
     * @var string
     */
    private $newPackageName;

    /**
     * @var string
     */
    private $targetVersion;

    public function __construct(string $oldPackageName, string $newPackageName, string $targetVersion)
    {
        Assert::notSame(
            $oldPackageName,
            $newPackageName,
            '$oldPackageName cannot be the same as $newPackageName. If you want to change version of package, use ' . ChangePackageVersion::class
        );

        $this->oldPackageName = $oldPackageName;
        $this->newPackageName = $newPackageName;
        $this->targetVersion = $targetVersion;
    }

    public function modify(ComposerJson $composerJson): void
    {
        $composerJson->replacePackage($this->oldPackageName, $this->newPackageName, $this->targetVersion);
    }
}
