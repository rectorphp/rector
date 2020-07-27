<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

interface UpdateDocName
{
    /**
     * @param Identifier|Name $value
     */
    public function method(Node $value);
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

interface UpdateDocName
{
    /**
     * @param Identifier|Name $node
     */
    public function method(Node $node);
}

?>
