<?php

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

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

namespace Rector\CodeQuality\Tests\Rector\Concat\JoinStringConcatRector\Fixture;

class SingleConcat
{
    public function run()
    {
        $name = 'Hi Tom';
    }
}

?>
