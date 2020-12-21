<?php

namespace Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class SkipNonExistingClass
{
    public function run()
    {
        return '<?php if (Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\SomeClassThatDoesntExist::staticCall()) { ?>';
    }
}

?>
