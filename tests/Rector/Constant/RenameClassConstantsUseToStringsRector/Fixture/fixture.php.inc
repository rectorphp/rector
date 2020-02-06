<?php

namespace Rector\Core\Tests\Rector\Constant\RenameClassConstantsUseToStringsRector\Fixture;

use Rector\Core\Tests\Rector\Constant\RenameClassConstantsUseToStringsRector\Source\OldClassWithConstants;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return OldClassWithConstants::DEVELOPMENT;
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\Constant\RenameClassConstantsUseToStringsRector\Fixture;

use Rector\Core\Tests\Rector\Constant\RenameClassConstantsUseToStringsRector\Source\OldClassWithConstants;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return 'development';
    }
}

?>
