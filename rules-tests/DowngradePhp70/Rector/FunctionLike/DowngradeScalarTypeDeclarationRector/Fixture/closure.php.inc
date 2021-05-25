<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

do_something(function ( string $to_return ) : string {
    return $to_return;
});

function do_something($returner) {
  $returned = $returner('abc123');
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

do_something(function ( $to_return ) {
    $to_return = (string) $to_return;
    return $to_return;
});

function do_something($returner) {
  $returned = $returner('abc123');
}

?>