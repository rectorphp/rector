<?php

declare(strict_types=1);

namespace Rector\Testing\Configuration;

use Rector\Testing\Finder\RectorsFinder;
use Rector\Testing\PhpConfigPrinter\PhpConfigPrinterFactory;
use Symplify\PhpConfigPrinter\Printer\SmartPhpConfigPrinter;
use Symplify\SmartFileSystem\SmartFileSystem;

final class AllRectorConfigFactory
{
    /**
     * @var RectorsFinder
     */
    private $rectorsFinder;

    /**
     * @var SmartPhpConfigPrinter
     */
    private $smartPhpConfigPrinter;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var string
     */
    private $configFilePath;

    public function __construct()
    {
        $this->rectorsFinder = new RectorsFinder();
        $this->smartFileSystem = new SmartFileSystem();

        $phpConfigPrinterFactory = new PhpConfigPrinterFactory();
        $this->smartPhpConfigPrinter = $phpConfigPrinterFactory->create();

        $this->configFilePath = sys_get_temp_dir() . '/_rector_tests/all_rectors_config.php';
    }

    public function create(): string
    {
        $rectorClasses = $this->rectorsFinder->findCoreRectorClasses();
        $services = array_fill_keys($rectorClasses, null);

        $allRectorsContent = $this->smartPhpConfigPrinter->printConfiguredServices($services);
        $this->smartFileSystem->dumpFile($this->configFilePath, $allRectorsContent);

        return $this->configFilePath;
    }
}
