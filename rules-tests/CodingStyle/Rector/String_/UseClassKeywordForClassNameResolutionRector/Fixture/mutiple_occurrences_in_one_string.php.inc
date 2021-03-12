<?php

namespace Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class MutipleOccurrencesInOneString
{
    public function run()
    {
        return 'Rector\Tests\CodingStyle\Rector\Tests\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::staticCall() && Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::staticCall()';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class MutipleOccurrencesInOneString
{
    public function run()
    {
<<<<<<< HEAD
        return \Rector\Tests\CodingStyle\Rector\Tests\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::class . '::staticCall() && ' . \Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::class . '::staticCall()';
=======
        return \Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::class . '::staticCall() && Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\MutipleOccurrencesInOneString::staticCall()';
>>>>>>> 403441495... fixup! fixup! fixup! fixup! fixup! fixup! fixing
    }
}

?>
