<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

use PHPStan\PhpDocParser\Parser\PhpDocParser;

/**
 * @deprecated Use DoctrineAnnotation parser instead
 */
interface PhpDocParserAwareInterface
{
    public function setPhpDocParser(PhpDocParser $phpDocParser): void;
}
