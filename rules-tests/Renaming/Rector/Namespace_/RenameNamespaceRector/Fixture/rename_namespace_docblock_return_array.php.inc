<?php

namespace OldNamespace\SubNamespace;

use OldNamespace;

class RenameNamespaceDocblockReturnArray
{
    /**
     * @return \OldNamespace\SubNamespace\RenameNamespaceDocblockReturnArray[]
     */
    public function run(\OldNamespace\SubNamespace\RenameNamespaceDocblockReturnArray $argument)
    {
        return [$argument];
    }
}

?>
-----
<?php

namespace NewNamespace\SubNamespace;

use NewNamespace;

class RenameNamespaceDocblockReturnArray
{
    /**
     * @return \NewNamespace\SubNamespace\RenameNamespaceDocblockReturnArray[]
     */
    public function run(\NewNamespace\SubNamespace\RenameNamespaceDocblockReturnArray $argument)
    {
        return [$argument];
    }
}

?>
