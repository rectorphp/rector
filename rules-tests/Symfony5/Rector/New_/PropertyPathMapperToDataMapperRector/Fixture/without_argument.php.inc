<?php

namespace Rector\Tests\Symfony5\Rector\New_\PropertyPathMapperToDataMapperRector\Fixture;

use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class WithoutArgument
{
    public function run()
    {
        return new PropertyPathMapper();
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony5\Rector\New_\PropertyPathMapperToDataMapperRector\Fixture;

use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class WithoutArgument
{
    public function run()
    {
        return new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor());
    }
}

?>
