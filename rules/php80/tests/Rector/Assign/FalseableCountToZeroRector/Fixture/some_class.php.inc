<?php

namespace Rector\Php80\Tests\Rector\Assign\FalseableCountToZeroRector\Fixture;

class SomeClass
{
    public function run()
    {
        $value = pg_num_rows('...');
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Assign\FalseableCountToZeroRector\Fixture;

class SomeClass
{
    public function run()
    {
        $value = (int) pg_num_rows('...');
    }
}

?>
