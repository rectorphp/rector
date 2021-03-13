<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\Fixture;

class IncludeMethodCall
{
    /**
     * @var string
     */
    private $name;

    public function name(): string
    {
        return $this->name;
    }
}

class UsingMethodCall
{
    public function __construct(IncludeMethodCall $includeMethodCall)
    {
        $includeMethodCall->name();
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\MakeGetterClassMethodNameStartWithGetRector\Fixture;

class IncludeMethodCall
{
    /**
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }
}

class UsingMethodCall
{
    public function __construct(IncludeMethodCall $includeMethodCall)
    {
        $includeMethodCall->getName();
    }
}

?>
