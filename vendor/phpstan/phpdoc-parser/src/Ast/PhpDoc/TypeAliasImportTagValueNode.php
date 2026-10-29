<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NameNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class TypeAliasImportTagValueNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    public string $importedAlias;
    /** @var IdentifierTypeNode|NameNode */
    public TypeNode $importedFrom;
    public ?string $importedAs = null;
    /**
     * @param IdentifierTypeNode|NameNode $importedFrom
     */
    public function __construct(string $importedAlias, TypeNode $importedFrom, ?string $importedAs)
    {
        $this->importedAlias = $importedAlias;
        $this->importedFrom = $importedFrom;
        $this->importedAs = $importedAs;
    }
    public function __toString(): string
    {
        return trim("{$this->importedAlias} from {$this->importedFrom}" . ($this->importedAs !== null ? " as {$this->importedAs}" : ''));
    }
    /**
     * @param array<string, mixed> $properties
     */
    public static function __set_state(array $properties): self
    {
        $instance = new self($properties['importedAlias'], $properties['importedFrom'], $properties['importedAs']);
        if (isset($properties['attributes'])) {
            foreach ($properties['attributes'] as $key => $value) {
                $instance->setAttribute($key, $value);
            }
        }
        return $instance;
    }
}
