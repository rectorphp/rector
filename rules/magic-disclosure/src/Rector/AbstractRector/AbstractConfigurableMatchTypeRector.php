<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\AbstractRector;

use Rector\Core\Rector\AbstractRector;

abstract class AbstractConfigurableMatchTypeRector extends AbstractRector
{
    /**
     * @api
     * @var string
     */
    public const TYPES_TO_MATCH = 'types_to_match';

    /**
     * @var string[]
     */
    private $typesToMatch = [];

    /**
     * @param string[][] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->typesToMatch = $configuration[self::TYPES_TO_MATCH] ?? [];
    }

    protected function isMatchedType(string $currentType): bool
    {
        if ($this->typesToMatch === []) {
            return true;
        }

        foreach ($this->typesToMatch as $typeToMatch) {
            if (fnmatch($typeToMatch, $currentType, FNM_NOESCAPE)) {
                return true;
            }

            if (is_a($currentType, $typeToMatch, true)) {
                return true;
            }
        }

        return false;
    }
}
