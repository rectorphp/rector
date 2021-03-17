<?php

namespace Rector\Tests\CodeQuality\Rector\Concat\JoinStringConcatRector\Fixture;

class SingleConcat
{
    public function run()
    {
        $name = 'Hi' . ' Tom';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Concat\JoinStringConcatRector\Fixture;

class SingleConcat
{
    public function run()
    {
        $name = 'Hi Tom';
    }
}

?>
