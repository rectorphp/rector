<?php

namespace Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class EmptyStringBeforeClassName
{
    public function run()
    {
        return 'Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\EmptyStringBeforeClassName::staticCall()) { ?>';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class EmptyStringBeforeClassName
{
    public function run()
    {
        return \Rector\Tests\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\EmptyStringBeforeClassName::class . '::staticCall()) { ?>';
    }
}

?>
