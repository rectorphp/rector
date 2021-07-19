<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

/**
 * @method static static staticReturnStatic()
 * @method static static|StaticMethodInAnnotation|string[]|$this staticReturnMixed()
 * @method static withoutReturnType()
 * @method static withoutParameters
 * @method StaticMethodInAnnotation|static returnStatic
 */
trait StaticMethodInAnnotation
{
    public function run()
    {
        $this->staticReturnStatic();
        $this->staticReturnMixed();
        $this->withoutReturnType();
        $this->withoutParameters();
        $this->returnStatic();
    }
}

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector\Fixture;

/**
 * @method static static staticReturnStatic()
 * @method static static|StaticMethodInAnnotation|string[]|$this staticReturnMixed()
 * @method static withoutReturnType()
 * @method static withoutParameters
 * @method StaticMethodInAnnotation|static returnStatic
 */
trait StaticMethodInAnnotation
{
    public function run()
    {
        static::staticReturnStatic();
        static::staticReturnMixed();
        static::withoutReturnType();
        static::withoutParameters();
        $this->returnStatic();
    }
}

?>
