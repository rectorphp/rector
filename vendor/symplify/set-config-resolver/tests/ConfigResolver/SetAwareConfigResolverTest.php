<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SetConfigResolver\Tests\ConfigResolver;

use Iterator;
use RectorPrefix20210510\PHPUnit\Framework\TestCase;
use RectorPrefix20210510\Symfony\Component\Console\Input\ArrayInput;
use RectorPrefix20210510\Symplify\SetConfigResolver\Exception\SetNotFoundException;
use RectorPrefix20210510\Symplify\SetConfigResolver\SetAwareConfigResolver;
use RectorPrefix20210510\Symplify\SetConfigResolver\Tests\ConfigResolver\Source\DummySetProvider;
use RectorPrefix20210510\Symplify\SmartFileSystem\Exception\FileNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;
final class SetAwareConfigResolverTest extends TestCase
{
    /**
     * @var SetAwareConfigResolver
     */
    private $setAwareConfigResolver;
    protected function setUp() : void
    {
        $this->setAwareConfigResolver = new SetAwareConfigResolver(new DummySetProvider());
    }
    /**
     * @dataProvider provideOptionsAndExpectedConfig()
     * @param mixed[] $options
     */
    public function testDetectFromInputAndProvideWithAbsolutePath(array $options, string $expectedConfig) : void
    {
        $resolvedConfigFileInfo = $this->setAwareConfigResolver->resolveFromInput(new ArrayInput($options));
        $this->assertSame($expectedConfig, $resolvedConfigFileInfo->getRealPath());
    }
    public function provideOptionsAndExpectedConfig() : Iterator
    {
        (yield [['--config' => 'README.md'], \getcwd() . '/README.md']);
        (yield [['-c' => 'README.md'], \getcwd() . '/README.md']);
        (yield [['--config' => \getcwd() . '/README.md'], \getcwd() . '/README.md']);
        (yield [['-c' => \getcwd() . '/README.md'], \getcwd() . '/README.md']);
    }
    /**
     * @dataProvider provideDataForEmptyConfig()
     * @param mixed[] $options
     */
    public function testDetectFromInputAndProvideWithEmptyConfig(array $options) : void
    {
        $resolvedConfigFileInfo = $this->setAwareConfigResolver->resolveFromInput(new ArrayInput($options));
        $this->assertNull($resolvedConfigFileInfo);
    }
    /**
     * @return Iterator<array<int, array<int|string, string>>|null[]>
     */
    public function provideDataForEmptyConfig() : Iterator
    {
        (yield [['--', 'sh', '-c' => '/bin/true'], null]);
    }
    public function testSetsNotFound() : void
    {
        $this->expectException(SetNotFoundException::class);
        $basicConfigFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/missing_set_config.php');
        $this->setAwareConfigResolver->resolveFromParameterSetsFromConfigFiles([$basicConfigFileInfo]);
    }
    public function testPhpSetsFileInfos() : void
    {
        $basicConfigFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/php_config_with_sets.php');
        $setFileInfos = $this->setAwareConfigResolver->resolveFromParameterSetsFromConfigFiles([$basicConfigFileInfo]);
        $this->assertCount(1, $setFileInfos);
        $setFileInfo = $setFileInfos[0];
        $expectedSetFileInfo = new SmartFileInfo(__DIR__ . '/Source/some_php_set.php');
        $this->assertEquals($expectedSetFileInfo, $setFileInfo);
    }
    public function testMissingFileInInput() : void
    {
        $this->expectException(FileNotFoundException::class);
        $arrayInput = new ArrayInput(['--config' => 'someFile.yml']);
        $this->setAwareConfigResolver->resolveFromInput($arrayInput);
    }
}
