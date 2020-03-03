<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Webmozart\Assert\Assert;

final class SkipAssert
{
    /**
     * @return resource
     */
    public function stream()
    {
        $filePath = tempnam(sys_get_temp_dir(), 'test');
        $stream = fopen($filePath, 'rb');
        Assert::resource($stream, 'stream', sprintf('Could not stream content of local file: %s', $filePath));

        return $stream;
    }
}
