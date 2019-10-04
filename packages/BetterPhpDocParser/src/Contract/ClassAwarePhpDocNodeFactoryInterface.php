<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

interface ClassAwarePhpDocNodeFactoryInterface extends PhpDocNodeFactoryInterface
{
    public function getClass(): string;
}
