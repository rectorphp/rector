<?php

namespace Rector\Tests\DeadCode\Rector\MethodCall\RemoveEmptyMethodCallRector\Fixture;

class SkipFluentReturnThis2
{
    /**
     * @return $this
     */
    public static function prepare()
    {
        $self = new static();
        return $self;
    }

    public function run()
    {
    }

	public function call(string $class)
	{
        /** @var SkipFluentReturnThis2 $obj */
        $obj = new $class();
        $obj::prepare()->run();
	}
}

class Extended extends SkipFluentReturnThis2
{
    public function run()
    {
        echo 'hello';
    }
}

(new SkipFluentReturnThis2())->call(Extended::class);

?>
