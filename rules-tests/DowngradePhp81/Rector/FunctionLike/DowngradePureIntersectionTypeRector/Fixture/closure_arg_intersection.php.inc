<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use Baz\Bar;

$fn = function (Bar&\Qux $var) {
};

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use Baz\Bar;

$fn = function ($var) {
};

?>
