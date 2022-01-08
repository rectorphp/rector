<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector\Fixture;

class Fixture
{
    public function run()
    {
        return number_format(1000, 2, ',');
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FuncCall\DowngradeNumberFormatNoFourthArgRector\Fixture;

class Fixture
{
    public function run()
    {
        return number_format(1000, 2, ',', ',');
    }
}

?>
