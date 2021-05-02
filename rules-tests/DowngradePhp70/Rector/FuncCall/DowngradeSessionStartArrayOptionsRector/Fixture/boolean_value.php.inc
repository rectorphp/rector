<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\Fixture;

class BooleanValue
{
    public function start()
    {
        session_start([
            'use_trans_sid' => true,
        ]);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\Fixture;

class BooleanValue
{
    public function start()
    {
        ini_set('session.use_trans_sid', true);
        session_start();
    }
}

?>
