<?php

namespace Rector\Core\Tests\Issues\GetCalledClassToSelfAndStatic\Fixture;

class OnNonFinalClass
{
    public function run()
    {
        var_dump( get_called_class() );
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\GetCalledClassToSelfAndStatic\Fixture;

class OnNonFinalClass
{
    public function run()
    {
        var_dump( static::class );
    }
}

?>
