<?php

/**
 * @file
 * Tests event dispatching.
 */
namespace RectorPrefix20211231\cweagans\Composer\Tests;

use RectorPrefix20211231\cweagans\Composer\PatchEvent;
use RectorPrefix20211231\cweagans\Composer\PatchEvents;
use RectorPrefix20211231\Composer\Package\PackageInterface;
class PatchEventTest extends \RectorPrefix20211231\PHPUnit_Framework_TestCase
{
    /**
     * Tests all the getters.
     *
     * @dataProvider patchEventDataProvider
     */
    public function testGetters($event_name, \RectorPrefix20211231\Composer\Package\PackageInterface $package, $url, $description)
    {
        $patch_event = new \RectorPrefix20211231\cweagans\Composer\PatchEvent($event_name, $package, $url, $description);
        $this->assertEquals($event_name, $patch_event->getName());
        $this->assertEquals($package, $patch_event->getPackage());
        $this->assertEquals($url, $patch_event->getUrl());
        $this->assertEquals($description, $patch_event->getDescription());
    }
    public function patchEventDataProvider()
    {
        $prophecy = $this->prophesize('RectorPrefix20211231\\Composer\\Package\\PackageInterface');
        $package = $prophecy->reveal();
        return array(array(\RectorPrefix20211231\cweagans\Composer\PatchEvents::PRE_PATCH_APPLY, $package, 'https://www.drupal.org', 'A test patch'), array(\RectorPrefix20211231\cweagans\Composer\PatchEvents::POST_PATCH_APPLY, $package, 'https://www.drupal.org', 'A test patch'));
    }
}
