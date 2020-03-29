<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class FirstParent
{
    const CHILD_ONLY = true;
}

class SecondParent extends FirstParent
{
}

final class FirstBorn extends SecondParent
{
    public function run()
    {
        return parent::CHILD_ONLY;
    }
}

?>
-----
<?php

namespace Rector\Privatization\Tests\Rector\ClassConst\PrivatizeLocalClassConstantRector\Fixture;

class FirstParent
{
    protected const CHILD_ONLY = true;
}

class SecondParent extends FirstParent
{
}

final class FirstBorn extends SecondParent
{
    public function run()
    {
        return parent::CHILD_ONLY;
    }
}

?>
