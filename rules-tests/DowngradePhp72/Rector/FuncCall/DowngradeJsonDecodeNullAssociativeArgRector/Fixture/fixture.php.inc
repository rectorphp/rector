<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = json_decode('{}', null);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\Fixture;

class Fixture
{
    public function run()
    {
        $value = json_decode('{}', true);
    }
}

?>
