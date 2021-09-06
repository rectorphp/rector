<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class HasMethodType
{
    public $obj;

    public function loader($obj)
    {
        if (! method_exists($obj, 'findFile')) {
            return;
        }

        return $this->obj = $obj;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\PropertyTypeDeclarationRector\Fixture;

class HasMethodType
{
    /**
     * @var class-string|object|null
     */
    public $obj;

    public function loader($obj)
    {
        if (! method_exists($obj, 'findFile')) {
            return;
        }

        return $this->obj = $obj;
    }
}

?>
