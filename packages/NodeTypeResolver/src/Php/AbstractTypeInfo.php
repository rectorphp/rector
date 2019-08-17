<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Php;

use Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use Rector\Exception\ShouldNotHappenException;
use Rector\Php\TypeAnalyzer;
use Traversable;

abstract class AbstractTypeInfo
{
    /**
     * @var bool
     */
    protected $isNullable = false;

    /**
     * @var string[]
     */
    protected $types = [];

    /**
     * @var string[]
     */
    protected $typesToRemove = [];

    /**
     * @var string[]
     */
    protected $fqnTypes = [];

    /**
     * @var TypeAnalyzer
     */
    protected $typeAnalyzer;

    /**
     * @var string[]
     */
    private $iterableUnionTypes = [Traversable::class, '\Traversable', 'array'];

    /**
     * @var string[]
     */
    private $removedTypes = [];

    /**
     * @param string[] $types
     * @param string[] $fqnTypes
     */
    public function __construct(array $types, TypeAnalyzer $typeAnalyzer, array $fqnTypes = [])
    {
        $this->typeAnalyzer = $typeAnalyzer;
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

    public function getTypeCount(): int
    {
        return count($this->types);
    }

    /**
     * @return Name|NullableType|Identifier|null
     */
    public function getTypeNode(bool $forceFqn = false)
    {
        if (! $this->isTypehintAble()) {
            return null;
        }

        $type = $this->resolveTypeForTypehint($forceFqn);
        if ($type === null) {
            throw new ShouldNotHappenException();
        }

        // normalize for type-declaration
        if (Strings::endsWith($type, '[]')) {
            $type = 'array';
        }

        if ($this->typeAnalyzer->isPhpReservedType($type)) {
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
        if ($this->hasRemovedTypes()) {
            return false;
        }

        // are object subtypes
        if ($this->areMutualObjectSubtypes($this->types)) {
            return true;
        }

        $typeCount = count($this->types);

        if ($typeCount >= 2 && $this->isArraySubtype($this->types)) {
            return true;
        }

        return $typeCount === 1;
    }

    /**
     * @return string[]
     */
    public function getDocTypes(): array
    {
        $allTypes = array_merge($this->types, $this->removedTypes);

        if ($this->isNullable) {
            $allTypes[] = 'null';
        }

        $uniqeueTypes = array_filter(array_unique($allTypes));

        $uniqeueTypes = $this->removeIterableTypeIfTraversableType($uniqeueTypes);

        // use mixed[] over array, that is more explicit about implicitnes
        if ($uniqeueTypes === ['array']) {
            return ['mixed[]'];
        }

        // remove void types, as its useless in annotation
        foreach ($uniqeueTypes as $key => $value) {
            if ($value === 'void') {
                unset($uniqeueTypes[$key]);
            }
        }

        return $uniqeueTypes;
    }

    protected function normalizeName(string $name): string
    {
        return ltrim($name, '$');
    }

    /**
     * @param string[] $types
     */
    protected function isArraySubtype(array $types): bool
    {
        if ($types === []) {
            return false;
        }

        foreach ($types as $type) {
            if (in_array($type, ['array', 'iterable'], true)) {
                continue;
            }

            if (Strings::endsWith($type, '[]')) {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * @param string|string[] $types
     * @return string[]
     */
    private function analyzeAndNormalizeTypes($types): array
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
            if (in_array($type, ['mixed', 'static'], true)) {
                unset($types[$i]);
                $this->removedTypes[] = $type;
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

            if ($type === 'object' && ! $this->typeAnalyzer->isPhpSupported('object')) {
                $this->removedTypes[] = $type;
                unset($types[$i]);
                continue;
            }

            $types[$i] = $this->typeAnalyzer->normalizeType($type);
        }

        // remove undesired types
        $types = $this->removeTypes($types);

        $types = $this->squashTraversableAndArrayToIterable($types);

        $types = array_unique($types);

        // re-index to add expected behavior
        return array_values($types);
    }

    private function hasRemovedTypes(): bool
    {
        return count($this->removedTypes) > 1;
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
        $types = explode('|', $type);
        foreach ($types as $key => $singleType) {
            if ($this->typeAnalyzer->isPhpReservedType($singleType) || strtolower($singleType) === '$this') {
                $types[$key] = strtolower($singleType);
            }
        }

        return implode($types, '|');
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
                $this->removedTypes[] = $type;
                unset($types[$i]);
            }
        }

        return $types;
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
     */
    private function areMutualObjectSubtypes(array $types): bool
    {
        return $this->resolveMutualObjectSubtype($types) !== null;
    }

    /**
     * @param string[] $types
     */
    private function resolveMutualObjectSubtype(array $types): ?string
    {
        foreach ($types as $type) {
            if ($this->classLikeExists($type)) {
                return null;
            }

            foreach ($types as $subloopType) {
                if (! is_a($subloopType, $type, true)) {
                    continue 2;
                }
            }

            return $type;
        }

        return null;
    }

    private function resolveTypeForTypehint(bool $forceFqn): ?string
    {
        if ($this->areMutualObjectSubtypes($this->types)) {
            return $this->resolveMutualObjectSubtype($this->types);
        }

        if (in_array('iterable', $this->types, true)) {
            return 'iterable';
        }

        $types = $forceFqn ? $this->fqnTypes : $this->types;

        return $types[0];
    }

    private function classLikeExists(string $type): bool
    {
        return ! class_exists($type) && ! interface_exists($type) && ! trait_exists($type);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function removeIterableTypeIfTraversableType(array $types): array
    {
        if (count($types) <= 1) {
            return $types;
        }

        foreach ($types as $key => $uniqeueType) {
            // remove iterable if other types are provided
            if ($uniqeueType === 'iterable') {
                unset($types[$key]);
            }
        }

        return $types;
    }
}
