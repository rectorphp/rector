<?php

declare(strict_types=1);

namespace Rector\Core\Rector\AbstractRector;

use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;

/**
 * This could be part of @see AbstractRector, but decopuling to trait
 * makes clear what code has 1 purpose.
 */
trait PhpDocTrait
{
    /**
     * @var PhpDocInfoFactory
     */
    protected $phpDocInfoFactory;

    /**
     * @required
     */
    public function autowirePhpDocTrait(PhpDocInfoFactory $phpDocInfoFactory): void
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
}
