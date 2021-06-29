<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class DieSome
{
    public function run()
    {
        echo 100;
        die;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class DieSome
{
    /**
     * @return never
     */
    public function run()
    {
        echo 100;
        die;
    }
}

?>
