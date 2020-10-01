<?php

declare(strict_types=1);

namespace Rector\Restoration\NameMatcher;

use Nette\Utils\Strings;
use PhpParser\Node\Name\FullyQualified;
use Rector\Restoration\ClassMap\ExistingClassesProvider;

final class FullyQualifiedNameMatcher
{
    /**
     * @var ExistingClassesProvider
     */
    private $existingClassesProvider;

    public function __construct(ExistingClassesProvider $existingClassesProvider)
    {
        $this->existingClassesProvider = $existingClassesProvider;
    }

    public function matchFullyQualifiedName(string $desiredShortName): ?FullyQualified
    {
        foreach ($this->existingClassesProvider->provide() as $declaredClass) {
            $declaredShortClass = (string) Strings::after($declaredClass, '\\', -1);
            if ($declaredShortClass !== $desiredShortName) {
                continue;
            }

            return new FullyQualified($declaredClass);
        }

        return null;
    }
}
