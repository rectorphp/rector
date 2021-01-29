<?php

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallRector\Fixture;

$aRandomVar = false;

ini_get('safe_mode');
ini_set('y2k_compliance', 1);
ini_set('magic_quotes_runtime', 0);
ini_set('safe_mode', $aRandomVar);

?>
-----
<?php

namespace Rector\Removing\Tests\Rector\FuncCall\RemoveFuncCallRector\Fixture;

$aRandomVar = false;

?>
