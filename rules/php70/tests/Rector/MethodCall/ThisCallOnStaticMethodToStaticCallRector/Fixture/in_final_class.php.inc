<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

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

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

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
