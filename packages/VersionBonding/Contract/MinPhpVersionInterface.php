<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\VersionBonding\Contract;

use RectorPrefix20220606\Rector\Core\Contract\Rector\RectorInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersion;
/**
 * Can be implemented by @see RectorInterface
 * All rules that do not meet this PHP version will not be run and user will be warned about it.
 * They can either:
 *      - exclude rule,
 *      - bump PHP version in composer.json or
 *      - use Option::PHP_VERSION_FEATURES parameter in rector.php
 */
interface MinPhpVersionInterface
{
    /**
     * @todo upgrade to Enum and return of Enum object in the future.
     * Requires refactoring of PhpVersion and PhpVersionFeatures object at the same time.
     */
    public function provideMinPhpVersion() : int;
}
