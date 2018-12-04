<?php

$type = 'bool';
// callable and iterable are not property typehintable
// @see https://wiki.php.net/rfc/typed_properties_v2#supported_types
if (in_array($type, ['callable', 'void'], true)) {
    return false;
}

return true;

?>
-----
<?php

$type = 'bool';
// callable and iterable are not property typehintable
// @see https://wiki.php.net/rfc/typed_properties_v2#supported_types
return !in_array($type, ['callable', 'void'], true);

?>
