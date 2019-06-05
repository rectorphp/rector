<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class SkipSensitive
{
    public function run()
    {
        return ['exception' => 'Exception'];
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\String_\StringClassNameToClassConstantRector\Fixture;

class SkipSensitive
{
    public function run()
    {
        return ['exception' => \Exception::class];
    }
}

?>
