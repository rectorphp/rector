<?php

namespace Foo2;

class Foo2 extends FooBar
{
    /**
     * @param \DateTime $foo
     *
     * @return Storage|\Storage
     */
    public function bar($foo){}
}

?>
-----
<?php

namespace Foo2;

use DateTime;
class Foo2 extends FooBar
{
    /**
     * @param DateTime $foo
     *
     * @return Storage|\Illuminate\Support\Facades\Storage
     */
    public function bar($foo){}
}

?>
