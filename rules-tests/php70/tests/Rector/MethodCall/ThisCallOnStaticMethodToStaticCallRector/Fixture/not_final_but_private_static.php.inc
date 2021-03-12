<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class NotFinalButPrivateStatic
{
    public function speak()
    {
        return $this->whoami();
    }

    private static function whoami()
    {
        return 'parent';
    }
}

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

class NotFinalButPrivateStatic
{
    public function speak()
    {
        return self::whoami();
    }

    private static function whoami()
    {
        return 'parent';
    }
}

?>
