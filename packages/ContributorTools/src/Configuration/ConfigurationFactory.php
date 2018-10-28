<?php declare(strict_types=1);

namespace Rector\ContributorTools\Configuration;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;
use Rector\ContributorTools\Exception\ConfigurationException;
use Rector\Exception\FileSystem\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use function Safe\sprintf;

final class ConfigurationFactory
{
    /**
     * @var string
     */
    private $levelsDirectory;

    public function __construct()
    {
        $this->levelsDirectory = __DIR__ . '/../../../../config/level';
    }

    public function createFromConfigFile(string $configFile): Configuration
    {
        $this->ensureConfigFileIsFound($configFile);

        $config = (array) Yaml::parseFile($configFile);
        $this->ensureConfigIsValid($config, $configFile);

        $fqnNodeTypes = $this->resolveFullyQualifiedNodeTypes($config['node_types']);
        $category = $this->resolveCategoryFromFqnNodeTypes($fqnNodeTypes);

        return new Configuration(
            $config['package'],
            $config['name'],
            $category,
            $this->resolveFullyQualifiedNodeTypes($config['node_types']),
            $config['description'],
            $config['code_before'],
            $config['code_after'],
            array_filter((array) $config['source']),
            $this->resolveLevelConfig($config['level'])
        );
    }

    private function ensureConfigFileIsFound(string $configFile): void
    {
        if (file_exists($configFile)) {
            return;
        }

        throw new FileNotFoundException(sprintf('First, create config file "%s"', $configFile));
    }

    /**
     * @param mixed[] $config
     */
    private function ensureConfigIsValid(array $config, string $configFile): void
    {
        $requiredKeys = [
            'package',
            'name',
            'node_types',
            'code_before',
            'code_after',
            'description',
            'source',
            'level',
        ];

        if (count(array_intersect(array_keys($config), $requiredKeys)) === count($requiredKeys)) {
            if (count($config['node_types']) < 1) {
                throw new ConfigurationException(sprintf(
                    '"%s" option requires at least one node, e.g. "FuncCall"',
                    'node_types'
                ));
            }

            return;
        }

        throw new ConfigurationException(sprintf(
            'Make sure "%s" keys are present in the "%s" config',
            implode('", "', $requiredKeys),
            $configFile
        ));
    }

    /**
     * @param string[] $nodeTypes
     * @return string[]
     */
    private function resolveFullyQualifiedNodeTypes(array $nodeTypes): array
    {
        $nodeClasses = $this->getNodeClasses();

        $fqnNodeTypes = [];
        foreach ($nodeTypes as $nodeType) {
            foreach ($nodeClasses as $nodeClass) {
                if ($this->isNodeClassMatch($nodeClass, $nodeType)) {
                    $fqnNodeTypes[] = $nodeClass;
                    continue 2;
                }
            }
        }

        return array_unique($fqnNodeTypes);
    }

    /**
     * @param string[] $fqnNodeTypes
     */
    private function resolveCategoryFromFqnNodeTypes(array $fqnNodeTypes): string
    {
        $fqnNodeType = $fqnNodeTypes[0];

        return Strings::after($fqnNodeType, '\\', -1);
    }

    /**
     * @return string[]
     */
    private function getNodeClasses(): array
    {
        $robotLoader = new RobotLoader();
        $robotLoader->addDirectory(__DIR__ . '/../../../../vendor/nikic/php-parser/lib/PhpParser/Node');
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_robotloader_nodes');
        $robotLoader->rebuild();

        return array_keys($robotLoader->getIndexedClasses());
    }

    private function isNodeClassMatch(string $nodeClass, string $nodeType): bool
    {
        if (Strings::endsWith($nodeClass, '\\' . $nodeType)) {
            return true;
        }

        // in case of forgotten _
        return Strings::endsWith($nodeClass, '\\' . $nodeType . '_');
    }

    private function resolveLevelConfig(string $level): ?string
    {
        if (! $level) {
            return null;
        }

        $finder = Finder::create()->files()
            ->in($this->levelsDirectory)
            ->name(sprintf('#%s$#', $level));

        /** @var SplFileInfo[] $fileInfos */
        $fileInfos = iterator_to_array($finder->getIterator());
        if (! count($fileInfos)) {
            return null;
        }

        /** @var SplFileInfo $foundLevelConfigFileInfo */
        $foundLevelConfigFileInfo = array_pop($fileInfos);
        return $foundLevelConfigFileInfo->getRealPath();
    }
}
