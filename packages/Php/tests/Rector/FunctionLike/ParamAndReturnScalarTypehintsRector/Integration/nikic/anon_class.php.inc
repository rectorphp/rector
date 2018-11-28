<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\AnonClass;

new class {
    /** @param int $a */
    public function test($a) {}
};

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Integration\AnonClass;

new class
{
    /** @param int $a */
    public function test(int $a) {}
};

?>
