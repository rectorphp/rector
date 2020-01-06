<?php

namespace Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfRector\Fixture;

class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        if (function_exists('session_abort')) {
            session_abort();
        } else {
            session_write_close();
        }
    }
}

?>
-----
<?php

namespace Rector\Polyfill\Tests\Rector\If_\UnwrapFutureCompatibleIfRector\Fixture;

class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        session_abort();
    }
}

?>
