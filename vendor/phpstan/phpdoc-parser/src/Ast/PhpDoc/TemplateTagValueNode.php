<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class TemplateTagValueNode implements \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    /** @var non-empty-string */
    public $name;
    /** @var TypeNode|null */
    public $bound;
    /** @var TypeNode|null */
    public $lowerBound;
    /** @var TypeNode|null */
    public $default;
    /** @var string (may be empty) */
    public $description;
    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name, ?TypeNode $bound, string $description, ?TypeNode $default = null, ?TypeNode $lowerBound = null)
    {
        $this->name = $name;
        $this->bound = $bound;
        $this->lowerBound = $lowerBound;
        $this->default = $default;
        $this->description = $description;
    }
    public function __toString() : string
    {
        $upperBound = $this->bound !== null ? " of {$this->bound}" : '';
        $lowerBound = $this->lowerBound !== null ? " super {$this->lowerBound}" : '';
        $default = $this->default !== null ? " = {$this->default}" : '';
        return trim("{$this->name}{$upperBound}{$lowerBound}{$default} {$this->description}");
    }
}
