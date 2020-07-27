<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

interface UpdateDocName
{
    /**
     * @param Identifier|Name $value
     */
    public function method(\PhpParser\Node $value);
}

?>
-----
<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use ReflectionProperty;

interface UpdateDocName
{
    /**
     * @param Identifier|Name $node
     */
    public function method(\PhpParser\Node $node);
}

?>
