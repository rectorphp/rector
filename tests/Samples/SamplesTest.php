<?php

namespace Rector\Core\Tests\Samples;

use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Parser\NikicPhpParserFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\Testing\Finder\RectorsFinder;
use Rector\Testing\PhpConfigPrinter\PhpConfigPrinterFactory;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileSystem;

class SamplesTest extends AbstractKernelTestCase
{
    /** @var SmartFileSystem */
    private $smartFileSystem;

    protected function setUp(): void
    {
        $this->smartFileSystem = new SmartFileSystem();
    }

    public function testSamples()
    {
        $this->createContainerWithAllRectors();

        $parserFactory = self::$container->get(NikicPhpParserFactory::class);
        $parser = $parserFactory->create();
        $traverser = new NodeTraverser();
        $printer = new Standard();

        foreach (self::$container->getServiceIds() as $serviceId) {
            $service = self::$container->get($serviceId);
            if (!$service instanceof AbstractRector) {
                continue;
            }

            if (!$service->testSamples) {
                continue;
            }

            $traverser->addVisitor($service);
            $ruleDefinition = $service->getRuleDefinition();
            foreach ($ruleDefinition->getCodeSamples() as $sample) {
                $badCode = $sample->getBadCode();
                if (strpos($badCode, '<?php') === false) {
                    $badCode = "<?php\n" . $badCode;
                }

                $nodes = $parser->parse($badCode);
                $newNodes = $traverser->traverse($nodes);
                $this->assertEquals($sample->getGoodCode(), $printer->prettyPrint($newNodes));
            }
            $traverser->removeVisitor($service);
        }
    }

    private function createContainerWithAllRectors(): void
    {
        $servicesFinder = new RectorsFinder();
        $coreRectorClasses = $servicesFinder->findCoreRectorClasses();

        $listForConfig = [];
        foreach ($coreRectorClasses as $serviceClass) {
            $listForConfig[$serviceClass] = null;
        }

        $filePath = sprintf(sys_get_temp_dir() . '/rector_sample_tests/all_rectors.php');
        $this->createPhpConfigFileAndDumpToPath($listForConfig, $filePath);

        $this->bootKernelWithConfigs(RectorKernel::class, [$filePath]);
    }

    private function createPhpConfigFileAndDumpToPath(array $serviceClassesWithConfiguration, string $filePath): void
    {
        $phpConfigPrinterFactory = new PhpConfigPrinterFactory();
        $smartPhpConfigPrinter = $phpConfigPrinterFactory->create();

        $fileContent = $smartPhpConfigPrinter->printConfiguredServices($serviceClassesWithConfiguration);
        $this->smartFileSystem->dumpFile($filePath, $fileContent);
    }
}
