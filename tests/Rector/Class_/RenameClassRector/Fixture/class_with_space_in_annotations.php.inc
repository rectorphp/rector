<?php

namespace MyNamespace2;

class MyClass
{
    /**
     * Useful when you need to create new Client multiple times in your script.
     * 
     * This is a workaround.
     * Current (2017-12) gearman extension version doesn't disconnect when you unset() this object :(
     * I also tried to call this in __destruct(), but it doesn't work. Must be called explicitly.
     */
    public function create() { }
}

?>
-----
<?php

namespace MyNamespace2;

class MyClass
{
    /**
     * Useful when you need to create new Client multiple times in your script.
     * 
     * This is a workaround.
     * Current (2017-12) gearman extension version doesn't disconnect when you unset() this object :(
     * I also tried to call this in __destruct(), but it doesn't work. Must be called explicitly.
     */
    public function create() { }
}

?>
