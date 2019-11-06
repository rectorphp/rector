<?php

function identicalFalseToBooleanNot($something)
{
    if (!$something === false) {
    }

    if ((!$one && $two) === false) {
        return null;
    }
}

?>
-----
<?php

function identicalFalseToBooleanNot($something)
{
    if ($something) {
    }

    if ((!$one && $two) === false) {
        return null;
    }
}

?>
