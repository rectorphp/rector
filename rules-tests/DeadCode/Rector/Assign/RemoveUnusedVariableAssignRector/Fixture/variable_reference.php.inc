<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class VariableReference
{
    public function create_signature()
    {
        $pkeyid = "some_public_key";

		    $ok = openssl_sign("blah", $signature, $pkeyid);

		    return $signature;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector\Fixture;

final class VariableReference
{
    public function create_signature()
    {
        $pkeyid = "some_public_key";

		    openssl_sign("blah", $signature, $pkeyid);

		    return $signature;
    }
}

?>
