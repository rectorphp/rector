<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\PackageBuilder\Composer;

use RectorPrefix20210510\Jean85\Exception\ReplacedPackageException;
use RectorPrefix20210510\Jean85\PrettyVersions;
use RectorPrefix20210510\Jean85\Version;
use OutOfBoundsException;
use RectorPrefix20210510\PharIo\Version\InvalidVersionException;
final class PackageVersionProvider
{
    /**
     * Returns current version of package, contains only major and minor.
     */
    public function provide(string $packageName) : string
    {
        try {
            $version = $this->getVersion($packageName, 'symplify/symplify');
            return $version->getPrettyVersion() ?: 'Unknown';
        } catch (\OutOfBoundsException|\RectorPrefix20210510\PharIo\Version\InvalidVersionException $exceptoin) {
            return 'Unknown';
        }
    }
    /**
     * Workaround for when the required package is executed in the monorepo or replaced in any other way
     *
     * @see https://github.com/symplify/symplify/pull/2901#issuecomment-771536136
     * @see https://github.com/Jean85/pretty-package-versions/pull/16#issuecomment-620550459
     */
    private function getVersion(string $packageName, string $replacingPackageName) : \RectorPrefix20210510\Jean85\Version
    {
        try {
            return \RectorPrefix20210510\Jean85\PrettyVersions::getVersion($packageName);
        } catch (\OutOfBoundsException|\RectorPrefix20210510\Jean85\Exception\ReplacedPackageException $exception) {
            return \RectorPrefix20210510\Jean85\PrettyVersions::getVersion($replacingPackageName);
        }
    }
}
