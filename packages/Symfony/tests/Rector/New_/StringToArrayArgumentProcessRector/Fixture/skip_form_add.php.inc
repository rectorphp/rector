<?php

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use stdClass;

class SkipFormAdd
{
    public function run(FormBuilderInterface $formBuilder)
    {
        $formBuilder->add('q', stdClass::class);

    }
}

interface FormBuilderInterface
{
    /**
     * @param string|FormBuilderInterface $child
     * @param string|null                 $type
     */
    public function add($child, $type = null, array $options = []);
}
