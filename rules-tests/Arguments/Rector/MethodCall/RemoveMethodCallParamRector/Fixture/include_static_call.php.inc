<?php

namespace Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Fixture;

use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\StaticCaller;

StaticCaller::remove(1, 2, 3, 4);

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Fixture;

use Rector\Tests\Arguments\Rector\MethodCall\RemoveMethodCallParamRector\Source\StaticCaller;

StaticCaller::remove(1, 2, 3);

?>
