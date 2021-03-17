<?php

namespace Rector\Tests\Symfony5\Rector\New_\PropertyPathMapperToDataMapperRector\Fixture;

use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class WithArgument
{
    public function run()
    {
        return new PropertyPathMapper(new PropertyAccessor());
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony5\Rector\New_\PropertyPathMapperToDataMapperRector\Fixture;

use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class WithArgument
{
    public function run()
    {
        return new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor(new PropertyAccessor()));
    }
}

?>
