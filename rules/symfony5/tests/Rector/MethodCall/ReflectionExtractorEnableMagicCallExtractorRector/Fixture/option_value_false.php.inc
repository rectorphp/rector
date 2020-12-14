<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector\Fixture;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class OptionValueFalse
{
    public function run()
    {
        $reflectionExtractor = new ReflectionExtractor();
        $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar', [
            'enable_magic_call_extraction' => false,
        ]);
    }
}

?>
-----
<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector\Fixture;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class OptionValueFalse
{
    public function run()
    {
        $reflectionExtractor = new ReflectionExtractor();
        $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar', [
            'enable_magic_methods_extraction' => \Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor::MAGIC_GET | \Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor::MAGIC_SET,
        ]);
    }
}

?>
