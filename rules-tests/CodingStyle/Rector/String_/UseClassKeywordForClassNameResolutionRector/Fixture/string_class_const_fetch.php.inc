<?php

namespace Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class AnotherClass
{
    public function get()
    {
        return '\Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Source\ConstantReferenceClass::HI';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class AnotherClass
{
    public function get()
    {
        return \Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Source\ConstantReferenceClass::class . '::HI';
    }
}

?>
