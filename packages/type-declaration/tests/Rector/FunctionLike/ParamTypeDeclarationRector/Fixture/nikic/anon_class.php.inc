<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\AnonClass;

new class {
    /** @param int $a */
    public function test($a) {}
};

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ParamTypeDeclarationRector\Fixture\AnonClass;

new class {
    /** @param int $a */
    public function test(int $a) {}
};

?>
