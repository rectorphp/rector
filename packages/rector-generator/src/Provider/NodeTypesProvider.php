<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Provider;

use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @see \Rector\RectorGenerator\Tests\Provider\NodeTypesProviderTest
 */
final class NodeTypesProvider
{
    /**
     * @var string
     */
    private const PHP_PARSER_NODES_PATH = __DIR__ . '/../../../../vendor/nikic/php-parser/lib/PhpParser/Node';

    /**
     * @var string
     */
    private const PHP_PARSER_NAMESPACE = 'PhpParser\Node\\';

    /**
     * @return array<string, string>
     */
    public function provide(): array
    {
        $finder = new Finder();
        $finder = $finder
            ->files()
            ->in(self::PHP_PARSER_NODES_PATH);

        $fileInfos = iterator_to_array($finder->getIterator());

        $nodeTypes = [];
        foreach ($fileInfos as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            $name = str_replace(['.php', '/'], ['', '\\'], $fileInfo->getRelativePathname());

            $reflectionClass = new ReflectionClass(self::PHP_PARSER_NAMESPACE . $name);
            if ($reflectionClass->isAbstract()) {
                continue;
            }
            if ($reflectionClass->isInterface()) {
                continue;
            }

            $nodeTypes[$name] = $name;
        }

        return $nodeTypes;
    }
}
