<?php

namespace Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class Fixture
{
    public function run()
    {
        return '<?php if (Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\Fixture::staticCall()) { ?>';
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture;

class Fixture
{
    public function run()
    {
        return '<?php if (' . \Rector\CodingStyle\Tests\Rector\String_\UseClassKeywordForClassNameResolutionRector\Fixture\Fixture::class . '::staticCall()) { ?>';
    }
}

?>
