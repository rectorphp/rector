<?php

namespace OldNamespace\SubNamespace;

use OldNamespace;

class RenameNamespaceDocblockReturn
{
    /**
     * @return \OldNamespace\SubNamespace\RenameNamespaceDocblockReturn
     */
    public function run(\OldNamespace\SubNamespace\RenameNamespaceDocblockReturn $argument)
    {
        return $argument;
    }
}

?>
-----
<?php

namespace NewNamespace\SubNamespace;

use NewNamespace;

class RenameNamespaceDocblockReturn
{
    /**
     * @return \NewNamespace\SubNamespace\RenameNamespaceDocblockReturn
     */
    public function run(\NewNamespace\SubNamespace\RenameNamespaceDocblockReturn $argument)
    {
        return $argument;
    }
}

?>
