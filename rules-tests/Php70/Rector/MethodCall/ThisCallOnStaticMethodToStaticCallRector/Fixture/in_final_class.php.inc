<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

final class InFinalClass
{
    public function speak()
    {
        return $this->whoami();
    }
    public static function whoami()
    {
        return 'parent';
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

final class InFinalClass
{
    public function speak()
    {
        return self::whoami();
    }
    public static function whoami()
    {
        return 'parent';
    }
}

?>
