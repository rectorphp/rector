<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\AbstractRector;

use PhpParser\Node;
use PhpParser\Node\Expr;
use Rector\Core\Rector\AbstractRector;
use Rector\MagicDisclosure\Matcher\ClassNameTypeMatcher;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @todo upgrade Rector core rule
 */
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
     * @var ClassNameTypeMatcher
     */
    private $classNameTypeMatcher;

    /**
     * @required
     */
    public function autowireAbstractConfigurableMatchTypeRector(ClassNameTypeMatcher $classNameTypeMatcher): void
    {
        $this->classNameTypeMatcher = $classNameTypeMatcher;
    }

    /**
     * @param string[][] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->typesToMatch = $configuration[self::TYPES_TO_MATCH] ?? [];
    }

    protected function isInMatchedClassName(Node $node): bool
    {
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return true;
        }

        return $this->isMatchedType($className);
    }

    protected function isExprMatchingAllowedTypes(Expr $expr): bool
    {
        return $this->classNameTypeMatcher->doesExprMatchNames($expr, $this->typesToMatch);
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
        }

        return false;
    }
}
