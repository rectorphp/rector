<?php

class EnumNoNamespace
{
    public const LEFT = 'left';

    public const RIGHT = 'right';
}

?>
-----
<?php

enum EnumNoNamespace : string
{
    case LEFT = 'left';
    case RIGHT = 'right';
}

?>
