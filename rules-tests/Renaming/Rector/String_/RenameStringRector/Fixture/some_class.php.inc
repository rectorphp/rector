<?php

namespace Rector\Tests\Renaming\Rector\String_\RenameStringRector\Fixture;

class SomeClass
{
    public function run()
    {
        return 'ROLE_PREVIOUS_ADMIN';
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\String_\RenameStringRector\Fixture;

class SomeClass
{
    public function run()
    {
        return 'IS_IMPERSONATOR';
    }
}

?>
