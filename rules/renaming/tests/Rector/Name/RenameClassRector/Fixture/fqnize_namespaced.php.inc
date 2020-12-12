<?php

class FqnizeNamespaced
{
    public function run()
    {
        return new DateTime();
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

class FqnizeNamespaced
{
    public function run()
    {
        return new \DateTime();
    }
}

?>
