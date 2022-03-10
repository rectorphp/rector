<?php

namespace OldNamespace\SubNamespace;

use OldNamespace;

class RenameNamespaceDocblockParam
{
    /**
     * @param \OldNamespace\SubNamespace\RenameNamespaceDocblockParam $argument
     */
    public function run(\OldNamespace\SubNamespace\RenameNamespaceDocblockParam $argument)
    {
        return $argument;
    }
}

?>
-----
<?php

namespace NewNamespace\SubNamespace;

use NewNamespace;

class RenameNamespaceDocblockParam
{
    /**
     * @param \NewNamespace\SubNamespace\RenameNamespaceDocblockParam $argument
     */
    public function run(\NewNamespace\SubNamespace\RenameNamespaceDocblockParam $argument)
    {
        return $argument;
    }
}

?>
