<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\FixtureIntersection;

final class StringIntersection
{
    public function execute()
    {
        $value = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename(__FILE__);
        $this->run($value);
    }

    private function run($value)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\FixtureIntersection;

final class StringIntersection
{
    public function execute()
    {
        $value = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename(__FILE__);
        $this->run($value);
    }

    private function run(string $value)
    {
    }
}

?>
