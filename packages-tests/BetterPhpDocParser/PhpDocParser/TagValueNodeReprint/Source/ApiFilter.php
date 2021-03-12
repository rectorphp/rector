<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Source;

use Rector\Core\Exception\ShouldNotHappenException;

/**
 * @Annotation
 * @Target({"PROPERTY", "CLASS"})
 */
class ApiFilter
{
    public function __construct($options = [])
    {
    }
}
