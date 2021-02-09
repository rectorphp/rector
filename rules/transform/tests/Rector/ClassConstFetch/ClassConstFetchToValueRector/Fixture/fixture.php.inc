<?php

namespace Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToValueRector\Fixture;

use Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToValueRector\Source\OldClassWithConstants;

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

namespace Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToValueRector\Fixture;

use Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToValueRector\Source\OldClassWithConstants;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return 'development';
    }
}

?>
