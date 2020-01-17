<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class Uri {
    public function __construct($value)
    {
    }
}

class DonTChangeOrder
{
    /**
     * @param mixed $value
     * @return string|\Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture\Uri|null
     */
    public function changeValue($value){
        if (null === $value || '' === $value) {
            return $value;
        }

        return new Uri($value);
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;


class Uri {
    public function __construct($value)
    {
    }
}

class DonTChangeOrder
{
    /**
     * @param mixed $value
     * @return string|\Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture\Uri|null
     */
    public function changeValue($value){
        if (null === $value || '' === $value) {
            return $value;
        }

        return new Uri($value);
    }
}

?>
