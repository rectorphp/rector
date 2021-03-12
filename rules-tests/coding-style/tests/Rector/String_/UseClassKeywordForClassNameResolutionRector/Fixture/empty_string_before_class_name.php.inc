<?php

namespace Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class EmptyStringBeforeClassName
{
    public function run()
    {
        return 'Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\EmptyStringBeforeClassName::staticCall()) { ?>';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class EmptyStringBeforeClassName
{
    public function run()
    {
        return \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\EmptyStringBeforeClassName::class . '::staticCall()) { ?>';
    }
}

?>
