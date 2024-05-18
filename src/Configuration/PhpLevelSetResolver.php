<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Exception\Configuration\InvalidConfigurationException;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;
final class PhpLevelSetResolver
{
    public static function resolveFromPhpVersion(int $phpVersion) : string
    {
        switch ($phpVersion) {
            case PhpVersion::PHP_53:
                return LevelSetList::UP_TO_PHP_53;
            case PhpVersion::PHP_54:
                return LevelSetList::UP_TO_PHP_54;
            case PhpVersion::PHP_55:
                return LevelSetList::UP_TO_PHP_55;
            case PhpVersion::PHP_56:
                return LevelSetList::UP_TO_PHP_56;
            case PhpVersion::PHP_70:
                return LevelSetList::UP_TO_PHP_70;
            case PhpVersion::PHP_71:
                return LevelSetList::UP_TO_PHP_71;
            case PhpVersion::PHP_72:
                return LevelSetList::UP_TO_PHP_72;
            case PhpVersion::PHP_73:
                return LevelSetList::UP_TO_PHP_73;
            case PhpVersion::PHP_74:
                return LevelSetList::UP_TO_PHP_74;
            case PhpVersion::PHP_80:
                return LevelSetList::UP_TO_PHP_80;
            case PhpVersion::PHP_81:
                return LevelSetList::UP_TO_PHP_81;
            case PhpVersion::PHP_82:
                return LevelSetList::UP_TO_PHP_82;
            case PhpVersion::PHP_83:
                return LevelSetList::UP_TO_PHP_83;
            case PhpVersion::PHP_84:
                return LevelSetList::UP_TO_PHP_84;
            default:
                throw new InvalidConfigurationException(\sprintf('Could not resolve PHP level set list for "%s"', $phpVersion));
        }
    }
}
