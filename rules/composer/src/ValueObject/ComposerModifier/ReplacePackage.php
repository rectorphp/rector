<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject\ComposerModifier;

use Rector\Composer\Rector\ChangePackageVersionRector;
use Webmozart\Assert\Assert;

/**
 * Replace one package for another
 * @see \Rector\Composer\Tests\ValueObject\ComposerModifier\ReplacePackageTest
 */
final class ReplacePackageAndVersion
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
            '$oldPackageName cannot be the same as $newPackageName. If you want to change version of package, use ' . ChangePackageVersionRector::class
        );

        $this->oldPackageName = $oldPackageName;
        $this->newPackageName = $newPackageName;
        $this->targetVersion = $targetVersion;
    }

    public function getOldPackageName(): string
    {
        return $this->oldPackageName;
    }

    public function getNewPackageName(): string
    {
        return $this->newPackageName;
    }

    public function getTargetVersion(): string
    {
        return $this->targetVersion;
    }
}
