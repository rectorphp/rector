<?php

declare (strict_types=1);
namespace Rector\VersionBonding\Contract;

use Rector\ValueObject\PhpVersion;
/**
 * Can be implemented by @see \Rector\Contract\Rector\RectorInterface
 *
 * Rules that do not meet this PHP version will be skipped.
 */
interface MinPhpVersionInterface
{
    /**
     * @return PhpVersion::*
     */
    public function provideMinPhpVersion() : int;
}
