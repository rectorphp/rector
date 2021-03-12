<?php

namespace Rector\Renaming\Tests\Rector\String_\RenameStringRector\Fixture;

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

namespace Rector\Renaming\Tests\Rector\String_\RenameStringRector\Fixture;

class SomeClass
{
    public function run()
    {
        return 'IS_IMPERSONATOR';
    }
}

?>
