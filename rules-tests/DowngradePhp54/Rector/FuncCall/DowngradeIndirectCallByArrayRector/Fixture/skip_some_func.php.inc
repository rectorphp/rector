<?php

namespace Rector\Tests\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector\Fixture;

$func = 'strtoupper';
echo $func('hello'); // not supposed to change

?>
