<?php

class MyOldClass
{
    /**
     * @return MyOldClass
     */
    public function createSelf(): MyOldClass
    {
        return new MyOldClass;
    }
}

?>
-----
<?php

namespace MyNamespace;

class MyNewClass
{
    /**
     * @return \MyNamespace\MyNewClass
     */
    public function createSelf(): \MyNamespace\MyNewClass
    {
        return new \MyNamespace\MyNewClass;
    }
}

?>
