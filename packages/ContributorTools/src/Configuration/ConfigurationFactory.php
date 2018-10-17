<?php declare(strict_types=1);

namespace Rector\ContributorTools\Configuration;

use Nette\Loaders\RobotLoader;
use Nette\Utils\Strings;
use Rector\ContributorTools\Exception\ConfigurationException;
use Rector\Exception\FileSystem\FileNotFoundException;
use Symfony\Component\Yaml\Yaml;
use function Safe\sprintf;

final class ConfigurationFactory
{
    public function createFromConfigFile(string $configFile): Configuration
    {
        $this->ensureConfigFileIsFound($configFile);

        $config = (array) Yaml::parseFile($configFile);
        $this->ensureConfigIsValid($config, $configFile);

        return new Configuration(
            $config['package'],
            $config['name'],
            $config['node_types'][0],
            $this->resolveFullyQualifiedNodeTypes($config['node_types']),
            $config['description'],
            $config['code_before'],
            $config['code_after'],
            $config['source']
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
        $requiredKeys = ['package', 'name', 'node_types', 'code_before', 'code_after', 'description', 'source'];
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
        $robotLoader = new RobotLoader();
        $robotLoader->addDirectory(__DIR__ . '/../../../../vendor/nikic/php-parser/lib/PhpParser/Node');
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_robotloader_nodes');
        $robotLoader->rebuild();

        $nodeClasses = array_keys($robotLoader->getIndexedClasses());

        $fqnNodeTypes = [];
        foreach ($nodeTypes as $nodeType) {
            foreach ($nodeClasses as $nodeClass) {
                if (Strings::endsWith($nodeClass, $nodeType)) {
                    $fqnNodeTypes[] = $nodeClass;
                }
            }
        }

        return array_unique($fqnNodeTypes);
    }
}
