<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class ExitSome
{
    public function run()
    {
        echo 100;
        exit;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class ExitSome
{
    /**
     * @return never
     */
    public function run()
    {
        echo 100;
        exit;
    }
}

?>
