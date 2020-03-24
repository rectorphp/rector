<?php

namespace Rector\Php74\Tests\Rector\Assign\NullCoalescingOperatorRector\Fixture;

$array = [];
$array['user_id'] = $array['user_id'] ?? 'value';
$array['user_id'] = $array['user_id_'] ?? 'value';

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Assign\NullCoalescingOperatorRector\Fixture;

$array = [];
$array['user_id'] ??= 'value';
$array['user_id'] = $array['user_id_'] ?? 'value';

?>
