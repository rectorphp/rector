<?php

namespace Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class Subcontent
{
    public function get()
    {
        /** @var int $trainings */
        $trainingsLonger = $this->getAnotherData();

        return $trainings;
    }
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\Fixture;

class Subcontent
{
    public function get()
    {
        $trainingsLonger = $this->getAnotherData();

        return $trainings;
    }
}

?>
