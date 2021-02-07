<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

final class ValueCallSetterOnly
{
    public $data;

    private $execstart;

    public function method(object $data)
    {
        $this->data[$data->key] = "data";
    }

    public function data()
    {
        $this->data;
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

final class ValueCallSetterOnly
{
    public $data;

    public function method(object $data)
    {
        $this->data[$data->key] = "data";
    }

    public function data()
    {
        $this->data;
    }
}

?>
