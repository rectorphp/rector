<?php

namespace Rector\Tests\Symfony\Rector\ClassMethod\NormalizeAutowireMethodNamingRector\Fixture;

final class SetAutowireClassname
{
    /** @required */
    public function foo()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony\Rector\ClassMethod\NormalizeAutowireMethodNamingRector\Fixture;

final class SetAutowireClassname
{
    /** @required */
    public function autowireSetAutowireClassname()
    {
    }
}

?>
