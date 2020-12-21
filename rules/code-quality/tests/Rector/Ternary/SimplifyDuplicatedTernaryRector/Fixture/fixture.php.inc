<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\SimplifyDuplicatedTernaryRector\Fixture;

class Fixture
{
    public function run(bool $value, string $name)
    {
         $isTrue = $value ? true : false;
         $isName = $name ? true : false;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\SimplifyDuplicatedTernaryRector\Fixture;

class Fixture
{
    public function run(bool $value, string $name)
    {
         $isTrue = $value;
         $isName = $name ? true : false;
    }
}

?>
