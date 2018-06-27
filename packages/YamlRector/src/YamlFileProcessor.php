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

    public function addYamlRector(YamlRectorInterface $yamlRector): void
    {
        $this->yamlRectors[] = $yamlRector;
    }

    public function processFileInfo(SplFileInfo $splFileInfo): string
    {
        $content = $splFileInfo->getContents();

        foreach ($this->yamlRectors as $yamlRector) {
            if (! $yamlRector->isCandidate($content)) {
                continue;
            }

            $content = $yamlRector->refactor($content);
        }

        return $content;
    }
}
