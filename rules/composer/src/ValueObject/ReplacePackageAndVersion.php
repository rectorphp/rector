<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Rector\ChangePackageVersionRector;
use Webmozart\Assert\Assert;

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
            'Old and new package have to be different. If you want to only change package version, use ' . ChangePackageVersionRector::class
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
