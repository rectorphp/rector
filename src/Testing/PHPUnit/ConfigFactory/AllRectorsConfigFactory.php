<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit\ConfigFactory;

use Nette\Utils\FileSystem;
use Rector\Testing\Finder\RectorsFinder;
use Symfony\Component\Yaml\Yaml;

final class AllRectorsConfigFactory
{
    /**
     * @var RectorsFinder
     */
    private $rectorsFinder;

    public function __construct()
    {
        $this->rectorsFinder = new RectorsFinder();
    }

    public function create(): string
    {
        $coreRectorClasses = $this->rectorsFinder->findCoreRectorClasses();

        $listForConfig = [];
        foreach ($coreRectorClasses as $rectorClass) {
            $listForConfig[$rectorClass] = null;
        }

        $yamlContent = Yaml::dump([
            'services' => $listForConfig,
        ], Yaml::DUMP_OBJECT_AS_MAP);

        $configFileTempPath = sprintf(sys_get_temp_dir() . '/rector_temp_tests/all_rectors.yaml');
        FileSystem::write($configFileTempPath, $yamlContent);

        return $configFileTempPath;
    }
}
