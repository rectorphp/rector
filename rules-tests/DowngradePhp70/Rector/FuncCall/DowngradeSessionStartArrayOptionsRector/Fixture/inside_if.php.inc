<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\Fixture;

class InsideIf
{
    public function start()
    {
        if (session_start([
            'cache_limiter' => 'private',
            'save_path' => '/tmp',
        ])) {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FuncCall\DowngradeSessionStartArrayOptionsRector\Fixture;

class InsideIf
{
    public function start()
    {
        ini_set('session.cache_limiter', 'private');
        ini_set('session.save_path', '/tmp');
        if (session_start()) {
        }
    }
}

?>
