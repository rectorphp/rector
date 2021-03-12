<?php

namespace Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector\Fixture;

class Keep
{
    public function run()
    {
        $name = 'Hi my name is Tom';
        $again = FALSE !== strpos($name, 'Hi', 2);
    }
}
