<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\FixtureModif;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

final class RouteName
{
    /**
     * @Route("/hello/")
     * @Template("some_path")
     */
    public function run()
    {
    }
}

?>
-----
/**
 * @Route("/hello/", methods={"GET", "HEAD"})
     * @Template("some_path")
     */
