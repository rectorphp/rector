<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class AnonClass
{
    private $class;

    public function __construct(int $bar)
    {
        $this->class = new class($bar)
        {
            public $bar;
            public function __construct(int $bar)
            {
                $this->bar = $bar;
            }
        };
    }
}

?>
    -----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\PropertyTypeDeclarationRector\Fixture;

class AnonClass
{
    private $class;

    public function __construct(int $bar)
    {
        $this->class = new class($bar)
        {
            /** @var int */
            public $bar;
            public function __construct(int $bar)
            {
                $this->bar = $bar;
            }
        };
    }
}

?>
