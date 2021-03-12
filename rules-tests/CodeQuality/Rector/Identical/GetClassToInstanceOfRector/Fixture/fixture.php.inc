<?php

function getClassToInstanceOf()
{
    $someObject = new stdClass();
    if ('stdClass' === get_class($someObject)) {
        echo 'yes!';
    }

    if (get_class($someObject) === stdClass::class) {
        echo 'yes!';
    }

    if ('stdClass' !== get_class($someObject)) {
        echo 'no!';
    }

    if (get_class($someObject) !== stdClass::class) {
        echo 'no!';
    }
}

?>
-----
<?php

function getClassToInstanceOf()
{
    $someObject = new stdClass();
    if ($someObject instanceof \stdClass) {
        echo 'yes!';
    }

    if ($someObject instanceof \stdClass) {
        echo 'yes!';
    }

    if (!$someObject instanceof \stdClass) {
        echo 'no!';
    }

    if (!$someObject instanceof \stdClass) {
        echo 'no!';
    }
}

?>
