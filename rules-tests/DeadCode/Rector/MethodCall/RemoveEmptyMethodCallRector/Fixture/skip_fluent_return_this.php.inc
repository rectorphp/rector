<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class SkipFluentReturnThis
{
    /**
     * @return $this
     */
    public function prepare()
    {
        return $this;
    }

    public function run()
    {
    }

	public function call(string $class)
	{
        /** @var SkipFluentReturnThis $obj */
        $obj = new $class();
        $obj->prepare()->run();
	}
}

class Extended extends SkipFluentReturnThis
{
    public function run()
    {
        echo 'hello';
    }
}

(new SkipFluentReturnThis())->call(Extended::class);

?>
