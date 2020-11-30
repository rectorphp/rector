<?php

declare(strict_types=1);

namespace Rector\Testing\PhpConfigPrinter;

use Symplify\PhpConfigPrinter\Contract\YamlFileContentProviderInterface;

final class YamlFileContentProvider implements YamlFileContentProviderInterface
{
    public function setContent(string $yamlContent): void
    {
    }

    public function getYamlContent(): string
    {
        return '';
    }
}
