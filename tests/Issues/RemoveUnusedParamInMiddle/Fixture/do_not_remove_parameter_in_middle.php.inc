<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RemoveUnusedParamInMiddle\Fixture;

final class DoNotRemoveParameterInMiddle
{
    private $propertyA;
    private $propertyB;
    private $propertyC;

    public function __construct($propertyA, $propertyB, $propertyC)
    {
        $this->propertyA = $propertyA;
        $this->propertyB = $propertyB;
        $this->propertyC = $propertyC;
    }

    public function run()
    {
        echo $this->propertyA;
        echo $this->propertyC;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RemoveUnusedParamInMiddle\Fixture;

final class DoNotRemoveParameterInMiddle
{
    private $propertyA;
    private $propertyC;

    public function __construct($propertyA, $propertyB, $propertyC)
    {
        $this->propertyA = $propertyA;
        $this->propertyC = $propertyC;
    }

    public function run()
    {
        echo $this->propertyA;
        echo $this->propertyC;
    }
}

?>
