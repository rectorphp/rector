<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Php;

use Rector\Php\TypeAnalyzer;

final class VarTypeInfo extends AbstractTypeInfo
{
    /**
     * Callable and iterable are not property typehintable
     * @see https://wiki.php.net/rfc/typed_properties_v2#supported_types
     *
     * @var string[]
     */
    protected $typesToRemove = ['callable', 'void'];

    public function isTypehintAble(): bool
    {
        if (count($this->types) !== 1) {
            return false;
        }

        $type = $this->getType();

        // first letter is upper, probably class type
        if (ctype_upper($type[0])) {
            return true;
        }
        return TypeAnalyzer::isPhpReservedType($type);
    }

    public function getType(): ?string
    {
        return $this->types[0] ?? null;
    }

    /**
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getFqnType(): ?string
    {
        return $this->fqnTypes[0] ?? null;
    }
}
