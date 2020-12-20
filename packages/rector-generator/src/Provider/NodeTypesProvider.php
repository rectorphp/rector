<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Provider;

use ReflectionClass;
use Symfony\Component\Finder\Finder;

final class NodeTypesProvider
{
    private const PHP_PARSER_NODES_PATH = __DIR__ . '/../../../../vendor/nikic/php-parser/lib/PhpParser/Node';

    private const PHP_PARSER_NAMESPACE = '\PhpParser\Node\\';

    /**
     * @var Finder
     */
    private $finder;

    public function __construct()
    {
        $this->finder = new Finder();
    }

    /**
     * @return array<string, string>
     */
    public function provide(): array
    {
        $filesList = $this->finder
            ->files()
            ->in(self::PHP_PARSER_NODES_PATH)
            ->getIterator()
        ;

        $names = [];
        foreach ($filesList as $splFileInfo) {
            $name = str_replace(['.php', '/'], ['', '\\'], $splFileInfo->getRelativePathname());

            $reflection = new ReflectionClass(self::PHP_PARSER_NAMESPACE . $name);
            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            $names[$name] = $name;
        }

        return $names;
    }
}
