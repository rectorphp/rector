<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InConstructorCall
{
    public function getObject(): object
    {
        $codeSampleFrom = '...';
        $codeSampleTo = '...';
        return new ConfiguredCodeSample($codeSampleFrom, $codeSampleTo, );
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

class InConstructorCall
{
    public function getObject(): object
    {
        $codeSampleFrom = '...';
        $codeSampleTo = '...';
        return new ConfiguredCodeSample($codeSampleFrom, $codeSampleTo);
    }
}

?>
