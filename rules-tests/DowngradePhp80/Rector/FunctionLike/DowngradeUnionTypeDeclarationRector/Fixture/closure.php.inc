<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

$ids = array_map(
    function (int | string $id) {

    },
    $ids
);

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeUnionTypeDeclarationRector\Fixture;

$ids = array_map(
    function ($id) {

    },
    $ids
);

?>