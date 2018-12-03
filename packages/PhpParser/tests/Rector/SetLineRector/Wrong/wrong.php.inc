<?php

namespace Rector\PhpParser\Tests\Rector\SetLineRector\Wrong;

use PhpParser\Node\Scalar\String_;

function setLine()
{
    $node = new String_('hey');
    $node->setLine(5);
}

?>
-----
<?php

namespace Rector\PhpParser\Tests\Rector\SetLineRector\Wrong;

use PhpParser\Node\Scalar\String_;

function setLine()
{
    $node = new String_('hey');
    $node->setAttribute('line', 5);
}

?>
