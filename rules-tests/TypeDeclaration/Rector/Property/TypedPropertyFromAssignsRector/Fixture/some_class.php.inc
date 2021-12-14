<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class SomeClass
{
    private $name;

    public function run()
    {
        $this->name = 'string';
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\Fixture;

final class SomeClass
{
    private ?string $name = null;

    public function run()
    {
        $this->name = 'string';
    }
}

?>
