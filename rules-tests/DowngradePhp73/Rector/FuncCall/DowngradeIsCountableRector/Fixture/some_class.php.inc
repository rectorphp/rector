<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector\Fixture;

$items = [];
return is_countable($items);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector\Fixture;

$items = [];
return is_array($items) || $items instanceof \Countable;

?>
