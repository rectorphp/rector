<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector\Fixture;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class OptionValueTrue
{
    public function run()
    {
        $reflectionExtractor = new ReflectionExtractor();
        $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar', [
            'enable_magic_call_extraction' => true,
        ]);
    }
}

?>
-----
<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector\Fixture;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class OptionValueTrue
{
    public function run()
    {
        $reflectionExtractor = new ReflectionExtractor();
        $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar', [
            'enable_magic_methods_extraction' => \Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor::MAGIC_CALL | \Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor::MAGIC_GET | \Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor::MAGIC_SET,
        ]);
    }
}

?>
