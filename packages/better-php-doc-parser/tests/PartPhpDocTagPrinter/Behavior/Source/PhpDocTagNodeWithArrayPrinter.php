<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PartPhpDocTagPrinter\Behavior\Source;

use Rector\BetterPhpDocParser\PartPhpDocTagPrinter\Behavior\ArrayPartPhpDocTagPrinterTrait;

final class PhpDocTagNodeWithArrayPrinter
{
    use ArrayPartPhpDocTagPrinterTrait;
}
