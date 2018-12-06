<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Php;

use Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use Rector\Php\PhpTypeSupport;
use Rector\Php\TypeAnalyzer;
use Traversable;
use function Safe\sort;

abstract class AbstractTypeInfo
{
    /**
     * @var string[]
     */
    protected $types = [];

    /**
     * @var string[]
     */
    protected $typesToRemove = [];

    /**
     * @var bool
     */
    protected $isNullable = false;

    /**
     * @var string[]
     */
    protected $fqnTypes = [];

    /**
     * @var bool
     */
    protected $hasRemovedTypes = false;

    /**
     * @var string[]
     */
    private $iterableUnionTypes = [Traversable::class, '\Traversable', 'array'];

    /**
     * @param string[] $types
     * @param string[] $fqnTypes
     */
    public function __construct(array $types, array $fqnTypes = [])
    {
        $this->types = $this->analyzeAndNormalizeTypes($types);

        // fallback
        if ($fqnTypes === []) {
            $fqnTypes = $types;
        }

        $this->fqnTypes = $this->analyzeAndNormalizeTypes($fqnTypes);
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * @return Name|NullableType|Identifier|null
     */
    public function getFqnTypeNode()
    {
        return $this->getTypeNode(true);
    }

    /**
     * @return Name|NullableType|Identifier|null
     */
    public function getTypeNode(bool $forceFqn = false)
    {
        $types = $forceFqn ? $this->fqnTypes : $this->types;

        if (! $this->isTypehintAble()) {
            return null;
        }

        $type = $types[0];

        if (TypeAnalyzer::isPhpReservedType($type)) {
            if ($this->isNullable) {
                return new NullableType($type);
            }

            return new Identifier($type);
        }

        $name = $forceFqn ? new FullyQualified($type) : new Name($type);

        if ($this->isNullable) {
            return new NullableType($name);
        }

        return $name;
    }

    /**
     * Can be put as PHP typehint to code
     */
    public function isTypehintAble(): bool
    {
        if ($this->hasRemovedTypes) {
            return false;
        }

        $typeCount = count($this->types);

        if ($typeCount >= 2 && $this->isArraySubtype($this->types)) {
            return true;
        }

        return $typeCount === 1;
    }

    protected function normalizeName(string $name): string
    {
        return ltrim($name, '$');
    }

    /**
     * @param string|string[] $types
     * @return string[]
     */
    protected function analyzeAndNormalizeTypes($types): array
    {
        $types = (array) $types;

        foreach ($types as $i => $type) {
            // convert: ?Type => Type, null
            $type = $this->normalizeNullable($type);
            $type = $this->normalizeCasing($type);

            if ($type === 'null') {
                unset($types[$i]);
                $this->isNullable = true;
                continue;
            }

            // remove
            if (in_array($type, ['static', 'mixed'], true)) {
                unset($types[$i]);
                continue;
            }

            if (in_array($type, ['true', 'false'], true)) {
                $types[$i] = 'bool';
                continue;
            }

            if ($type === '$this') {
                $types[$i] = 'self';
                continue;
            }

            if ($type === 'object' && PhpTypeSupport::isTypeSupported('object') === false) {
                unset($types[$i]);
                continue;
            }

            $types[$i] = TypeAnalyzer::normalizeType($type);
        }

        // remove undesired types
        $types = $this->removeTypes($types);

        $types = $this->squashTraversableAndArrayToIterable($types);

        $types = array_unique($types);

        // re-index to add expected behavior
        return array_values($types);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function squashTraversableAndArrayToIterable(array $types): array
    {
        // Traversable | array = iterable
        if (count(array_intersect($this->iterableUnionTypes, $types)) !== 2) {
            return $types;
        }

        foreach ($types as $i => $type) {
            if (in_array($type, $this->iterableUnionTypes, true)) {
                unset($types[$i]);
            }
        }

        $types[] = 'iterable';

        return $types;
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function removeTypes(array $types): array
    {
        if ($this->typesToRemove === []) {
            return $types;
        }

        foreach ($types as $i => $type) {
            if (in_array($type, $this->typesToRemove, true)) {
                $this->hasRemovedTypes = true;
                unset($types[$i]);
            }
        }

        return $types;
    }

    /**
     * @param string[] $types
     */
    private function isArraySubtype(array $types): bool
    {
        $arraySubtypeGroup = ['array', 'iterable'];

        if ($this->areArraysEqual($types, $arraySubtypeGroup)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed[] $types
     * @param mixed[] $arraySubtypeGroup
     */
    private function areArraysEqual(array $types, array $arraySubtypeGroup): bool
    {
        sort($types);
        sort($arraySubtypeGroup);

        return $types === $arraySubtypeGroup;
    }

    private function normalizeNullable(string $type): string
    {
        if (Strings::startsWith($type, '?')) {
            $type = ltrim($type, '?');
            $this->isNullable = true;
        }
        return $type;
    }

    private function normalizeCasing(string $type): string
    {
        if (TypeAnalyzer::isPhpReservedType($type)) {
            return strtolower($type);
        }

        if (strtolower($type) === ['$this']) {
            return strtolower($type);
        }

        return $type;
    }
}
