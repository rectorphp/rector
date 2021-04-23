<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class Fixture
{
    public function run()
    {
        $username = $_GET['user'] ?? 'nobody';
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\Coalesce\DowngradeStrictTypeDeclarationRector\Fixture;

class Fixture
{
    public function run()
    {
        $username = isset($_GET['user']) ? $_GET['user'] : 'nobody';
    }
}

?>
