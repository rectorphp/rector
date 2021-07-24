<?php

class Rocket {}

class NonNativeSimpleClassName
{
    private $data = ['Rocket' => 'Rocket Framework'];
}

?>
-----
<?php

class Rocket {}

class NonNativeSimpleClassName
{
    /**
     * @var array<string, string>
     */
    private $data = ['Rocket' => 'Rocket Framework'];
}

?>
