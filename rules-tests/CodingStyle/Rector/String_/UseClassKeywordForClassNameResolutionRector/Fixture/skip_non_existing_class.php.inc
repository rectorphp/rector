<?php

namespace Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class SkipNonExistingClass
{
    public function run()
    {
        return '<?php if (Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\SomeClassThatDoesntExist::staticCall()) { ?>';
    }
}

?>
