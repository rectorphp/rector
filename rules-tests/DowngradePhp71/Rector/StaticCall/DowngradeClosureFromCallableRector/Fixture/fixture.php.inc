<?php

namespace Rector\Tests\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector\Fixture;

$callable = 'used_callable_variable';
$lower = \Closure::fromCallable('strtolower');
$upper = \Closure::fromCallable('strtoupper');

?>
-----
<?php

namespace Rector\Tests\DowngradePhp71\Rector\StaticCall\DowngradeClosureFromCallableRector\Fixture;

$callable = 'used_callable_variable';
$callable2 = 'strtolower';
$lower = function () use ($callable2) {
    return $callable2(...func_get_args());
};
$callable2 = 'strtoupper';
$upper = function () use ($callable2) {
    return $callable2(...func_get_args());
};

?>
