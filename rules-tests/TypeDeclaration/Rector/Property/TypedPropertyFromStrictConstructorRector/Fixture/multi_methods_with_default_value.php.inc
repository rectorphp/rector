<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

final class MultiMethodsWithDefaultValue
{
    /**
     * @var string
     */
    private $rootView = 'admin.index';

    public function run()
    {
        $this->rootView = 'super_admin.index';
    }

    public function __construct()
    {
        $this->rootView = 'super_admin.index';
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\Fixture;

final class MultiMethodsWithDefaultValue
{
    private string $rootView;

    public function run()
    {
        $this->rootView = 'super_admin.index';
    }

    public function __construct()
    {
        $this->rootView = 'super_admin.index';
    }
}

?>
