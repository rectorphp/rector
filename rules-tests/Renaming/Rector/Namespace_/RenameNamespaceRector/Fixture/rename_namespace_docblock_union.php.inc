<?php

namespace OldNamespace\SubNamespace;

use OldNamespace;

class RenameNamespaceDocblockUnion
{
    public function run(?\OldNamespace\SubNamespace\RenameNamespaceDocblockUnion $argument)
    {
        /**
         * @var \OldNamespace\SubNamespace\RenameNamespaceDocblockUnion|null $argument
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

class RenameNamespaceDocblockUnion
{
    public function run(?\NewNamespace\SubNamespace\RenameNamespaceDocblockUnion $argument)
    {
        /**
         * @var \NewNamespace\SubNamespace\RenameNamespaceDocblockUnion|null $argument
         */
        $var = $argument;
        return $var;
    }
}

?>
