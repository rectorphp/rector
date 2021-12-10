<?php

#[\AllowDynamicProperties, \Immutable]
class RemoveAppendedAttribute
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

#[\Immutable]
class RemoveAppendedAttribute
{
    public string $someProperty = 'hello world';
}

?>
