<?php

namespace OldNamespace\SubNamespace;

use OldNamespace;

class SomeClass2
{
    public function someClass(OldNamespace\SubNamespace $argument)
    {
    }
}

?>
-----
<?php

namespace NewNamespace\SubNamespace;

use NewNamespace;

class SomeClass2
{
    public function someClass(\NewNamespace\SubNamespace $argument)
    {
    }
}

?>
