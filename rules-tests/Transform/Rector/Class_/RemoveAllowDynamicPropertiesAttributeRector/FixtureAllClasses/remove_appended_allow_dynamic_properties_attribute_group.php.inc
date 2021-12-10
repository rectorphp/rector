<?php

#[\AllowDynamicProperties]
#[\Immutable]
class RemoveAppendedAttributeGroup
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

#[\Immutable]
class RemoveAppendedAttributeGroup
{
    public string $someProperty = 'hello world';
}

?>
