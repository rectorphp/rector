<?php

namespace Foo;

class Foo extends FooBar
{
    /**
     * @param \DateTime $foo
     *
     * Storage here is Foo\Storage. Not \Storage.
     * @return Storage
     */
    public function bar($foo){}
}

?>
-----
<?php

namespace Foo;

use DateTime;
class Foo extends FooBar
{
    /**
     * @param DateTime $foo
     *
     * Storage here is Foo\Storage. Not \Storage.
     * @return Storage
     */
    public function bar($foo){}
}

?>
