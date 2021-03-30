<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\SensioTemplate;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Template("SomeClass::some.template.twig")
 */
final class SensioTemplateSimilarToConstant
{
}

?>
-----
PhpParser\Node\Stmt\Class_
-----
Sensio\Bundle\FrameworkExtraBundle\Configuration\Template
