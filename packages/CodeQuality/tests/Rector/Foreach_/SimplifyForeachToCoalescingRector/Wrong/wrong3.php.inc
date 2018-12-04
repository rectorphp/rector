<?php

$newValue = null;
$values = [];

foreach ($values as $key => $value) {
    if ($key === $input) {
        $newValue = $value;
    }
}

?>
-----
<?php

$newValue = null;
$values = [];

$newValue = $values[$value] ?? $newValue;

?>
