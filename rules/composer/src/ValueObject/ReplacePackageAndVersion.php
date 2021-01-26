<?php

declare(strict_types=1);

namespace Rector\Composer\ValueObject;

use Rector\Composer\Contract\VersionAwareInterface;
use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Webmozart\Assert\Assert;

final class ReplacePackageAndVersion implements VersionAwareInterface
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
    private $version;

    public function __construct(string $oldPackageName, string $newPackageName, string $version)
    {
        Assert::notSame(
            $oldPackageName,
            $newPackageName,
            'Old and new package have to be different. If you want to only change package version, use ' . ChangePackageVersionComposerRector::class
        );

        $this->oldPackageName = $oldPackageName;
        $this->newPackageName = $newPackageName;
        $this->version = $version;
    }

    public function getOldPackageName(): string
    {
        return $this->oldPackageName;
    }

    public function getNewPackageName(): string
    {
        return $this->newPackageName;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
