<?php

class NativeSimpleClassNames
{
    private $data = ['stdClass' => 'a simple class name'];
}

?>
-----
<?php

class NativeSimpleClassNames
{
    /**
     * @var array<class-string<\stdClass>, string>
     */
    private $data = ['stdClass' => 'a simple class name'];
}

?>
