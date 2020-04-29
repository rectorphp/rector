<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Source;

use Rector\Core\Exception\ShouldNotHappenException;

/**
 * @Annotation
 * @Target({"PROPERTY", "CLASS"})
 */
class ApiFilter
{
    public function __construct($options = [])
    {
        if(! class_exists($options['value'])) {
            throw new ShouldNotHappenException();
        }
    }
}
