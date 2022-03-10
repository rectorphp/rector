<?php

namespace OldNamespace\SubNamespace;

use OldNamespace;

class RenameNamespaceDocblockVar
{
    public function run(\OldNamespace\SubNamespace\RenameNamespaceDocblockVar $argument)
    {
        /**
         * @var \OldNamespace\SubNamespace\RenameNamespaceDocblockVar $argument
         */
        $var = $argument;
        return $var;
    }
}

?>
-----
<?php

namespace NewNamespace\SubNamespace;

use NewNamespace;

class RenameNamespaceDocblockVar
{
    public function run(\NewNamespace\SubNamespace\RenameNamespaceDocblockVar $argument)
    {
        /**
         * @var \NewNamespace\SubNamespace\RenameNamespaceDocblockVar $argument
         */
        $var = $argument;
        return $var;
    }
}

?>
