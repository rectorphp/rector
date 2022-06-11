<?php

namespace Foo2;

use Foo\Bar;

class Foo2 extends FooBar
{
    /**
     * @return Storage|\Storage
     */
    public function bar($foo){}
}

?>
-----
<?php

namespace Foo2;

use Foo\Bar;

class Foo2 extends FooBar
{
    /**
     * @return Storage|\Illuminate\Support\Facades\Storage
     */
    public function bar($foo){}
}

?>
