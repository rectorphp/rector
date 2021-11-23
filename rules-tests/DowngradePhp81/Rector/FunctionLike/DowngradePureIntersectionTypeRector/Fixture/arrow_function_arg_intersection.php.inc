<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use Baz\Bar;

$fn = fn (Bar&\Qux $var) => $var;

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

use Baz\Bar;

$fn = fn ($var) => $var;

?>
