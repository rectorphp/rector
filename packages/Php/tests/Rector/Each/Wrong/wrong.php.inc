<?php

function each1()
{
    while (list($module) = each($module_list)) {
    }

    while (list($key, $callback) = each($callbacks)) {
        // comment
    }

    $module_list = ['a', 'b'];

    while (list($module) = each($module_list)) {
        echo $module;
    }
}

?>
-----
<?php

function each1()
{
    foreach (array_keys($module_list) as $module) {
    }

    foreach ($callbacks as $key => $callback) {
        // comment
    }

    $module_list = ['a', 'b'];

    foreach (array_keys($module_list) as $module) {
        echo $module;
    }
}

?>
