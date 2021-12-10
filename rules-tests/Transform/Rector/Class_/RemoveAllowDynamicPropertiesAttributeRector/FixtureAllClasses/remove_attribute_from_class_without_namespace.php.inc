<?php

#[\AllowDynamicProperties]
class WantsAttributeRemoved
{
    public string $someProperty = 'hello world';
}

#[\AllowDynamicProperties]
class AlsoWantsAttributeRemoved
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

class WantsAttributeRemoved
{
    public string $someProperty = 'hello world';
}

class AlsoWantsAttributeRemoved
{
    public string $someProperty = 'hello world';
}

?>
