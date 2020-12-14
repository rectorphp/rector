<?php

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

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

namespace Rector\DowngradePhp80\Tests\Rector\ClassMethod\DowngradeTrailingCommasInParamUseRector\Fixture;

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
