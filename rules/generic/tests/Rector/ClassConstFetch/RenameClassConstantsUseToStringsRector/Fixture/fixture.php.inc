<?php

namespace Rector\Generic\Tests\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector\Fixture;

use Rector\Generic\Tests\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector\Source\OldClassWithConstants;

class Fixture
{
    public function getValue()
    {
        return OldClassWithConstants::DEVELOPMENT;
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector\Fixture;

use Rector\Generic\Tests\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector\Source\OldClassWithConstants;

class Fixture
{
    public function getValue()
    {
        return 'development';
    }
}

?>
