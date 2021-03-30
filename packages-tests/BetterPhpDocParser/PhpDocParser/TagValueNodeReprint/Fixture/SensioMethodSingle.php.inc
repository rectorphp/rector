<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\SensioMethod;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

final class SensioMethodSingle
{
    /**
     * @Method("POST")
     */
    public function run()
    {

    }
}

?>
-----
PhpParser\Node\Stmt\ClassMethod
-----
Sensio\Bundle\FrameworkExtraBundle\Configuration\Method
