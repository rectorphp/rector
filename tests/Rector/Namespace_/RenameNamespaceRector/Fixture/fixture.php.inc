<?php

namespace OldNamespace\SubNamespace;

use OldNamespace;
use OldNamespace\AnotherSubNamespace;

class SomeClass
{
    public function someClass()
    {
        $keepThis = new \OldNamespace;

        return new \OldNamespace\SomeClass;
    }
}

?>
-----
<?php

namespace NewNamespace\SubNamespace;

use NewNamespace;
use NewNamespace\AnotherSubNamespace;

class SomeClass
{
    public function someClass()
    {
        $keepThis = new \OldNamespace;

        return new \NewNamespace\SomeClass;
    }
}

?>
