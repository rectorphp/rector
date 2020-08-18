<?php

namespace MyNamespace;

interface MyInterface
{
    /**
     * @return MyInterface
     */
    public function createSelf(): MyInterface;
}

?>
-----
<?php

namespace MyNewNamespace;

interface MyNewInterface
{
    /**
     * @return \MyNewNamespace\MyNewInterface
     */
    public function createSelf(): \MyNewNamespace\MyNewInterface;
}

?>
