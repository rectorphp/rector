<?php

namespace Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector\Fixture;

class Fixture
{
    public function run()
    {
        // session locking trough other addons
        if (function_exists('session_abort')) {
            session_abort();
        } else {
            session_write_close();
        }
        
        // session locking trough other addons
        if (function_exists('session_abort')) {
            session_abort();
        }
    }
}

?>
-----
<?php

namespace Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector\Fixture;

class Fixture
{
    public function run()
    {
        // session locking trough other addons
        session_abort();
        
        // session locking trough other addons
        session_abort();
    }
}

?>
