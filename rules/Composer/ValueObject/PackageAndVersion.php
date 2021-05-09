<?php

declare (strict_types=1);
namespace Rector\Composer\ValueObject;

use Rector\Composer\Contract\VersionAwareInterface;
final class PackageAndVersion implements \Rector\Composer\Contract\VersionAwareInterface
{
    /**
     * @var string
     */
    private $packageName;
    /**
     * @var string
     */
    private $version;
    public function __construct(string $packageName, string $version)
    {
        $this->packageName = $packageName;
        $this->version = $version;
    }
    public function getPackageName() : string
    {
        return $this->packageName;
    }
    public function getVersion() : string
    {
        return $this->version;
    }
}
