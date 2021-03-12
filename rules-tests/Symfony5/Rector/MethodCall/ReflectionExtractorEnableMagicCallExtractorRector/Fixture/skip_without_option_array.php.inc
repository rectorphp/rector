<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector\Fixture;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class SkipWithoutOptionArray
{
    public function run()
    {
        $reflectionExtractor = new ReflectionExtractor();
        $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar');
    }
}
