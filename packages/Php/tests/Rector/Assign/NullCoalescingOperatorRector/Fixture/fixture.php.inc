<?php

$array = [];
$array['user_id'] = $array['user_id'] ?? 'value';
$array['user_id'] = $array['user_id_'] ?? 'value';

?>
-----
<?php

$array = [];
$array['user_id'] ??= 'value';
$array['user_id'] = $array['user_id_'] ?? 'value';

?>
