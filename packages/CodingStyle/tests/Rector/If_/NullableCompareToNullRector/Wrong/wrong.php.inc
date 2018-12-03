<?php

/** @var stdClass|null $value */
$value = null;
if ($value) {
}

if (!$value) {
}

?>
-----
<?php

/** @var stdClass|null $value */
$value = null;
if ($value !== null) {
}

if ($value === null) {
}

?>
