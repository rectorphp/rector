<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Nop;

final class NodeOverNodeAbstract
{
    public function run()
    {
        $for = new For_();
        $this->provideByType($for);

        $string = new String_('hey');
        $this->provideByType($string);
    }

    public function provideByType($node)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Nop;

final class NodeOverNodeAbstract
{
    public function run()
    {
        $for = new For_();
        $this->provideByType($for);

        $string = new String_('hey');
        $this->provideByType($string);
    }

    public function provideByType(\PhpParser\Node $node)
    {
    }
}

?>
