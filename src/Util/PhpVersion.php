<?php

declare(strict_types=1);

namespace Rector\Core\Util;

use Nette\Utils\Strings;

final class PhpVersion
{
    public static function getIntVersion(string $version): int
    {
        $explodeVersion = explode('.', $version);
        $countExplodedVersion = count($explodeVersion);

        if ($countExplodedVersion === 2) {
            return (int) $explodeVersion[0] * 10000 + (int) $explodeVersion[1] * 100;
        }

        if ($countExplodedVersion === 3) {
            return (int) $explodeVersion[0] * 10000 + (int) $explodeVersion[1] * 100 + (int) $explodeVersion[2];
        }

        return (int) $version;
    }
}
