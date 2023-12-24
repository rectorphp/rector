<?php

declare (strict_types=1);
namespace Rector\VersionBonding\Contract;

use Rector\Core\ValueObject\PolyfillPackage;
/**
 * Can be implemented by @see \Rector\Core\Contract\Rector\RectorInterface
 */
interface RelatedPolyfillInterface
{
    /**
     * @return PolyfillPackage::*
     */
    public function providePolyfillPackage() : string;
}
