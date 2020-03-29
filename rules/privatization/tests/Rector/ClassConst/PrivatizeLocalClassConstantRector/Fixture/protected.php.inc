<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class MakeConstantProtected
{
    const CHILD_ONLY = true;
}

class ConstantUser extends MakeConstantProtected
{
    public function run()
    {
        $is = self::CHILD_ONLY;
        $isAgain = parent::CHILD_ONLY;

        return static::CHILD_ONLY;
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class MakeConstantProtected
{
    protected const CHILD_ONLY = true;
}

class ConstantUser extends MakeConstantProtected
{
    public function run()
    {
        $is = self::CHILD_ONLY;
        $isAgain = parent::CHILD_ONLY;

        return static::CHILD_ONLY;
    }
}

?>
