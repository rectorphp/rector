<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class Fixture
{
    public function run()
    {
        compact('posts','units',);
        $this->setData('posts','units',);

        self::run('posts','units',);

        self::run('posts',
                  'units',);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector\Fixture;

class Fixture
{
    public function run()
    {
        compact('posts', 'units');
        $this->setData('posts', 'units');

        self::run('posts', 'units');

        self::run('posts', 'units');
    }
}

?>
