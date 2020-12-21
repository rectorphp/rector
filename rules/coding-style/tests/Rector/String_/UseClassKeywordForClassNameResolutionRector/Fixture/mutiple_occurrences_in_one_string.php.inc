<?php

namespace Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class MutipleOccurrencesInOneString
{
    public function run()
    {
        return 'Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::staticCall() && Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::staticCall()';
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
        return \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::class . '::staticCall() && ' . \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::class . '::staticCall()';
    }
}

?>
