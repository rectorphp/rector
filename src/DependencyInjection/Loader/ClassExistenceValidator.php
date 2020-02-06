<?php

declare(strict_types=1);

namespace Rector\Core\DependencyInjection\Loader;

use Nette\Utils\Strings;
use Rector\Core\Exception\DependencyInjection\ClassNotFoundException;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClassExistenceValidator
{
    /**
     * @var string
     */
    private const CLASSY_PATTERN = '#^[\\\\]?[A-Z]\w*(\\\\[A-Z]\w*)*$#';

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
            if (empty($class) || ! is_string($class)) {
                continue;
            }

            // not a class
            if (! Strings::match($class, self::CLASSY_PATTERN)) {
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
}
