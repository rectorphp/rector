<?php

function identicalFalseToBooleanNot()
{
    if ($something === false) {
    }

    if (false === $something) {
    }

    if (!$something === false) {
    }

    if ((!$one && $two) === false) {
        return null;
    }
}

?>
-----
<?php

function identicalFalseToBooleanNot()
{
    if (!$something) {
    }

    if (!$something) {
    }

    if ($something) {
    }

    if ((!$one && $two) === false) {
        return null;
    }
}

?>
