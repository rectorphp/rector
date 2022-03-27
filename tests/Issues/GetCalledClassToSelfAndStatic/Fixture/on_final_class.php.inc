<?php

namespace Rector\Core\Tests\Issues\GetCalledClassToSelfAndStatic\Fixture;

final class OnFinalClass
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

final class OnFinalClass
{
    public function run()
    {
        var_dump( self::class );
    }
}

?>
