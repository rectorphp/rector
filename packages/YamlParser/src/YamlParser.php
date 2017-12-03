<?php declare(strict_types=1);

namespace Rector\YamlParser;

use Nette\Utils\Strings;
use Rector\FileSystem\FileGuard;
use Symfony\Component\Yaml\Yaml;

final class YamlParser
{
    /**
     * @var string
     */
    private const NEW_ITEM_PATTERN = '#^[\s]{4}[a-zA-Z_]+#m';

    /**
     * @return mixed[]
     */
    public function parseFile(string $file): array
    {
        FileGuard::ensureFileExists($file, __METHOD__);

        return Yaml::parse(file_get_contents($file));
    }

    /**
     * @param mixed[] $data
     */
    public function getStringFromData(array $data): string
    {
        $result = Yaml::dump($data, 10);

        return $this->addEmptyLinesBetweenItems($result);
    }

    private function addEmptyLinesBetweenItems(string $result): string
    {
        $i = 0;
        return Strings::replace($result, self::NEW_ITEM_PATTERN, function ($match) use (&$i) {
            ++$i;
            if ($i === 1) {
                return $match[0];
            }

            return PHP_EOL . $match[0];
        });
    }
}
