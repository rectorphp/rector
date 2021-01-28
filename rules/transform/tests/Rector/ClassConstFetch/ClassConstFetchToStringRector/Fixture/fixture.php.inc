<?php

namespace Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToStringRector\Fixture;

use Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToStringRector\Source\OldClassWithConstants;

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

namespace Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToStringRector\Fixture;

use Rector\Transform\Tests\Rector\ClassConstFetch\ClassConstFetchToStringRector\Source\OldClassWithConstants;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return 'development';
    }
}

?>
