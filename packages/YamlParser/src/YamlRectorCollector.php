<?php declare(strict_types=1);

namespace Rector\YamlParser;

use Rector\YamlParser\Contract\Rector\YamlRectorInterface;

final class YamlRectorCollector
{
    /**
     * @var YamlParser
     */
    private $yamlParser;

    /**
     * @var YamlRectorInterface[]
     */
    private $yamlRectors = [];

    public function __construct(YamlParser $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    public function addYamlRector(YamlRectorInterface $yamlRector): void
    {
        $this->yamlRectors[] = $yamlRector;
    }

    public function processFile(string $file): string
    {
        $data = $this->yamlParser->parseFile($file);

        foreach ($this->yamlRectors as $yamlRector) {
            if (! array_key_exists($yamlRector->getCandidateKey(), $data)) {
                continue;
            }

            $key = $yamlRector->getCandidateKey();
            $data[$key] = $yamlRector->refactor($data[$key]);
        }

        return $this->yamlParser->getStringFromData($data);
    }
}
