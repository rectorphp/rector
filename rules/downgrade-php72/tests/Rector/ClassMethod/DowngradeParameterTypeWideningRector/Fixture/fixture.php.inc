<?php

namespace Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface A
{
    public function test(array $input);
}

class B implements A
{
    public function test($input){} // type omitted for $input
}

class C implements A
{
    public function test(array $input){}
}

?>
-----
<?php

namespace Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

interface A
{
    /**
     * @param mixed[] $input
     */
    public function test($input);
}

class B implements A
{
    public function test($input){} // type omitted for $input
}

class C implements A
{
    /**
     * @param mixed[] $input
     */
    public function test($input){}
}

?>
