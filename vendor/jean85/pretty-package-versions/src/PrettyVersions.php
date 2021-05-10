<?php

namespace RectorPrefix20210510\Jean85;

use RectorPrefix20210510\PackageVersions\Versions;
class PrettyVersions
{
    const SHORT_COMMIT_LENGTH = 7;
    public static function getVersion(string $packageName) : Version
    {
        return new Version($packageName, Versions::getVersion($packageName));
    }
    public static function getRootPackageName() : string
    {
        return Versions::ROOT_PACKAGE_NAME;
    }
    public static function getRootPackageVersion() : Version
    {
        return self::getVersion(Versions::ROOT_PACKAGE_NAME);
    }
}
