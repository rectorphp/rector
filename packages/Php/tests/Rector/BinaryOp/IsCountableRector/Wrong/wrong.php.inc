<?php

function isCountable()
{
    is_array($foo) || $foo instanceof Countable;
}

?>
-----
<?php

function isCountable()
{
    is_countable($foo);
}

?>
