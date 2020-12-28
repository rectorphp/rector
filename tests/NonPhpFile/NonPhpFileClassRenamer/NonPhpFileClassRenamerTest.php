<?php
declare(strict_types=1);

namespace Rector\Core\Tests\NonPhpFile\NonPhpFileClassRenamer;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\NonPhpFile\NonPhpFileClassRenamer;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Name\RenameClassRector\Source\OldClass;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NonPhpFileClassRenamerTest extends AbstractKernelTestCase
{
    /**
     * @var array<string, string>
     */
    private const CLASS_RENAMES = [
        'Session' => 'Illuminate\Support\Facades\Session',
        OldClass::class => NewClass::class,
        // Laravel
        'Form' => 'Collective\Html\FormFacade',
        'Html' => 'Collective\Html\HtmlFacade',
    ];

    /**
     * @var NonPhpFileClassRenamer
     */
    private $nonPhpFileClassRenamer;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);

        $this->nonPhpFileClassRenamer = $this->getService(NonPhpFileClassRenamer::class);
        $this->parameterProvider = $this->getService(ParameterProvider::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $this->parameterProvider->changeParameter(Option::AUTO_IMPORT_NAMES, false);

        $inputAndExpected = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fixtureFileInfo);

        $changedContent = $this->nonPhpFileClassRenamer->renameClasses(
            $inputAndExpected->getInput(),
            self::CLASS_RENAMES
        );

        $this->assertSame($inputAndExpected->getExpected(), $changedContent);
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '*');
    }
}
