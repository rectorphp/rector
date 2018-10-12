<?php declare(strict_types=1);

namespace Rector\DependencyInjection\Loader;

use Nette\Utils\Strings;
use Rector\Exception\DependencyInjection\ClassNotFoundException;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class ClassExistenceValidator
{
    /**
     * @var string
     */
    private const CLASS_PART_PATTERN = '[A-Z]\w*[a-z]\w*';

    /**
     * @var string
     */
    private const SERVICES_KEY = 'services';

    /**
     * @param mixed[] $configuration
     */
    public function ensureClassesAreValid(array $configuration, string $file): void
    {
        if (! isset($configuration[self::SERVICES_KEY])) {
            return;
        }

        foreach (array_keys($configuration[self::SERVICES_KEY]) as $class) {
            if (empty($class)) {
                continue;
            }

            // not a class
            if (! Strings::match($class, $this->getClassyPattern())) {
                continue;
            }

            if (interface_exists($class) || class_exists($class)) {
                continue;
            }

            throw new ClassNotFoundException(sprintf(
                'Class "%s" was not found while loading "%s" file. Are you sure it is correctly spelled?',
                $class,
                (new SmartFileInfo($file))->getRelativeFilePath()
            ));
        }
    }

    private function getClassyPattern(): string
    {
        return sprintf('#^%s(\\\\%s)+\z#', self::CLASS_PART_PATTERN, self::CLASS_PART_PATTERN);
    }
}
