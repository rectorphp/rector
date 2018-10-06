<?php declare(strict_types=1);

namespace Rector\YamlRector;

use Rector\YamlRector\Contract\YamlRectorInterface;
use Symfony\Component\Finder\SplFileInfo;

final class YamlFileProcessor
{
    /**
     * @var YamlRectorInterface[]
     */
    private $yamlRectors = [];

    /**
     * @param YamlRectorInterface[] $yamlRectors
     */
    public function __construct(array $yamlRectors = [])
    {
        $this->yamlRectors = $yamlRectors;
    }

    /**
     * @return YamlRectorInterface[]
     */
    public function getYamlRectors(): array
    {
        return $this->yamlRectors;
    }

    public function processFileInfo(SplFileInfo $splFileInfo): string
    {
        $content = $splFileInfo->getContents();

        foreach ($this->yamlRectors as $yamlRector) {
            $content = $yamlRector->refactor($content);
        }

        return $content;
    }

    public function getYamlRectorsCount(): int
    {
        return count($this->yamlRectors);
    }
}
