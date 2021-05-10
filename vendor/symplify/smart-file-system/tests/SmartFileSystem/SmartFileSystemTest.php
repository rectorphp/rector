<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SmartFileSystem\Tests\SmartFileSystem;

use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210510\Symplify\SmartFileSystem\SmartFileSystem;
final class SmartFileSystemTest extends TestCase
{
    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;
    protected function setUp() : void
    {
        $this->smartFileSystem = new SmartFileSystem();
    }
    public function testReadFileToSmartFileInfo() : void
    {
        $readFileToSmartFileInfo = $this->smartFileSystem->readFileToSmartFileInfo(__DIR__ . '/Source/file.txt');
        $this->assertInstanceof(SmartFileInfo::class, $readFileToSmartFileInfo);
    }
}
