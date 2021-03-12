<?php

namespace Rector\Tests\Transform\Rector\ClassConstFetch\ClassConstFetchToValueRector\Fixture;

use Rector\Tests\Transform\Rector\ClassConstFetch\ClassConstFetchToValueRector\Source\OldClassWithConstants;

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

namespace Rector\Tests\Transform\Rector\ClassConstFetch\ClassConstFetchToValueRector\Fixture;

use Rector\Tests\Transform\Rector\ClassConstFetch\ClassConstFetchToValueRector\Source\OldClassWithConstants;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return 'development';
    }
}

?>
