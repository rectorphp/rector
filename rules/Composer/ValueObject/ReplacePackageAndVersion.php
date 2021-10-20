<?php

declare (strict_types=1);
namespace Rector\Composer\ValueObject;

use Rector\Composer\Contract\VersionAwareInterface;
use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use RectorPrefix20211020\Webmozart\Assert\Assert;
final class ReplacePackageAndVersion implements \Rector\Composer\Contract\VersionAwareInterface
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
        $this->version = $version;
        \RectorPrefix20211020\Webmozart\Assert\Assert::notSame($oldPackageName, $newPackageName, 'Old and new package have to be different. If you want to only change package version, use ' . \Rector\Composer\Rector\ChangePackageVersionComposerRector::class);
        $this->oldPackageName = $oldPackageName;
        $this->newPackageName = $newPackageName;
    }
    public function getOldPackageName() : string
    {
        return $this->oldPackageName;
    }
    public function getNewPackageName() : string
    {
        return $this->newPackageName;
    }
    public function getVersion() : string
    {
        return $this->version;
    }
}
