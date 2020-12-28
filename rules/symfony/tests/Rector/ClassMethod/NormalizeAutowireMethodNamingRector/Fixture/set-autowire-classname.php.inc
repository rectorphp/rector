<?php

namespace Rector\Symfony\Tests\Rector\ClassMethod\NormalizeAutowireMethodNamingRector\Fixture;

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

namespace Rector\Symfony\Tests\Rector\ClassMethod\NormalizeAutowireMethodNamingRector\Fixture;

final class SetAutowireClassname
{
    /** @required */
    public function autowireSetAutowireClassname()
    {
    }
}

?>
