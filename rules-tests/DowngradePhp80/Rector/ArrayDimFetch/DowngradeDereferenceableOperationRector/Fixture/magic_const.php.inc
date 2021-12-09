<?php

namespace Rector\Tests\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector\Fixture;

var_dump(__DIR__[0] === '/');

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\ArrayDimFetch\DowngradeDereferenceableOperationRector\Fixture;

var_dump((__DIR__)[0] === '/');

?>
