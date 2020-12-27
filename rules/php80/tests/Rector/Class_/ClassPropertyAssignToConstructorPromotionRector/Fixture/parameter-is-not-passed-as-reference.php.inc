<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class ParameterIsNotPassedAsReference
{
    public $x;

    function __construct(string $x) {
        $x = 'preprended-' . $x;
        $this->x = $x;
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector\Fixture;

class ParameterIsNotPassedAsReference
{

    function __construct(public string $x) {
        $this->x = 'preprended-' . $x;
        $this->x = $x;
    }
}

?>
