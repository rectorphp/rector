<?php

declare (strict_types=1);
namespace Rector\VersionBonding\Contract;

/**
 * Can be implemented by @see \Rector\Core\Contract\Rector\RectorInterface
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
     * Requires refactoring of \Rector\Core\ValueObject\PhpVersion and \Rector\Core\ValueObject\PhpVersionFeature object at the same time.
     */
    public function provideMinPhpVersion() : int;
}
