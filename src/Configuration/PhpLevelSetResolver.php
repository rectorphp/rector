<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Set\ValueObject\SetList;
use Rector\Tests\Configuration\PhpLevelSetResolverTest;
use Rector\ValueObject\PhpVersion;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see PhpLevelSetResolverTest
 */
final class PhpLevelSetResolver
{
    /**
     * @var array<PhpVersion::*, SetList::PHP_*>
     */
    private const VERSION_LOWER_BOUND_CONFIGS = [PhpVersion::PHP_52 => SetList::PHP_52, PhpVersion::PHP_53 => SetList::PHP_53, PhpVersion::PHP_54 => SetList::PHP_54, PhpVersion::PHP_55 => SetList::PHP_55, PhpVersion::PHP_56 => SetList::PHP_56, PhpVersion::PHP_70 => SetList::PHP_70, PhpVersion::PHP_71 => SetList::PHP_71, PhpVersion::PHP_72 => SetList::PHP_72, PhpVersion::PHP_73 => SetList::PHP_73, PhpVersion::PHP_74 => SetList::PHP_74, PhpVersion::PHP_80 => SetList::PHP_80, PhpVersion::PHP_81 => SetList::PHP_81, PhpVersion::PHP_82 => SetList::PHP_82, PhpVersion::PHP_83 => SetList::PHP_83, PhpVersion::PHP_84 => SetList::PHP_84];
    /**
     * @param PhpVersion::* $phpVersion
     * @return string[]
     */
    public static function resolveFromPhpVersion(int $phpVersion) : array
    {
        $configFilePaths = [];
        foreach (self::VERSION_LOWER_BOUND_CONFIGS as $versionLowerBound => $phpSetFilePath) {
            if ($versionLowerBound <= $phpVersion) {
                $configFilePaths[] = $phpSetFilePath;
            }
        }
        Assert::allFileExists($configFilePaths);
        return $configFilePaths;
    }
}
