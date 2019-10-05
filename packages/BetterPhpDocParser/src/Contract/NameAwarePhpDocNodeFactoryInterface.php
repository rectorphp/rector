<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

interface NameAwarePhpDocNodeFactoryInterface extends PhpDocNodeFactoryInterface
{
    public function getName(): string;
}
