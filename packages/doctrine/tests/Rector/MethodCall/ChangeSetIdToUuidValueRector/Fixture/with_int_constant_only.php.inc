<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class WithIntConstantOnly
{
    const INTEGER_CONSTANT = 1;

    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setId(self::INTEGER_CONSTANT);

        // this values should be the same
        assert($buildingFirst->getId() === self::INTEGER_CONSTANT);
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class WithIntConstantOnly
{
    const INTEGER_CONSTANT = '%s-%s-%s-%s-%s';

    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setId(\Ramsey\Uuid\Uuid::fromString(self::INTEGER_CONSTANT));

        // this values should be the same
        assert($buildingFirst->getId() === self::INTEGER_CONSTANT);
    }
}

?>
