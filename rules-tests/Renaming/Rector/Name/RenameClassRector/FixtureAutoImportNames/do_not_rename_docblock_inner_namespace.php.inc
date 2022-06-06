<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use CompanyB\Storage;

class DoNotRenameDocblockInnerNamespace
{
    /**
     * @var Storage
     */
    protected $storage;

    public function __construct(
        Storage $storage
    ) {
        \Log::error('Some loggin');

        $this->storage        = $storage;
    }

    private function foo(): string
    {
        new Foo($this->storage);
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Log;
use CompanyB\Storage;

class DoNotRenameDocblockInnerNamespace
{
    /**
     * @var Storage
     */
    protected $storage;

    public function __construct(
        Storage $storage
    ) {
        Log::error('Some loggin');

        $this->storage        = $storage;
    }

    private function foo(): string
    {
        new Foo($this->storage);
    }
}

?>
