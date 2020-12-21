<?php

namespace Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class MutipleOccurrencesInOneString
{
    public function run()
    {
        return 'Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MultipleOccurrencesClass::staticCall() && Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MultipleOccurrencesClass::staticCall()';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class MutipleOccurrencesInOneString
{
    public function run()
    {
        return \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MultipleOccurrencesClass::class . '::staticCall() && ' . \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MultipleOccurrencesClass::class . '::staticCall()';
    }
}

?>
