<?php

namespace Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector\Fixture;

class Fixture
{
    public function run()
    {
        $name = 'Hi, my name is Tom';

        $again = FALSE !== strpos($name, 'Hi');
        $nope = FALSE === strpos($name, 'Hi');

        $andAgain = FALSE !== strpos($name, 'Hi', 0);

        return strpos($name, 'Hi') !== false;
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\NotIdentical\StrposToStringsContainsRector\Fixture;

class Fixture
{
    public function run()
    {
        $name = 'Hi, my name is Tom';

        $again = \Nette\Utils\Strings::contains($name, 'Hi');
        $nope = !\Nette\Utils\Strings::contains($name, 'Hi');

        $andAgain = \Nette\Utils\Strings::contains($name, 'Hi');

        return \Nette\Utils\Strings::contains($name, 'Hi');
    }
}

?>
