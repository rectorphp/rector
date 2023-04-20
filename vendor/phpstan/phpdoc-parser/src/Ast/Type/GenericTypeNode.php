<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use function implode;
use function sprintf;
class GenericTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    public const VARIANCE_INVARIANT = 'invariant';
    public const VARIANCE_COVARIANT = 'covariant';
    public const VARIANCE_CONTRAVARIANT = 'contravariant';
    public const VARIANCE_BIVARIANT = 'bivariant';
    use NodeAttributes;
    /** @var IdentifierTypeNode */
    public $type;
    /** @var TypeNode[] */
    public $genericTypes;
    /** @var (self::VARIANCE_*)[] */
    public $variances;
    /**
     * @param TypeNode[] $genericTypes
     * @param (self::VARIANCE_*)[] $variances
     */
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $type, array $genericTypes, array $variances = [])
    {
        $this->type = $type;
        $this->genericTypes = $genericTypes;
        $this->variances = $variances;
    }
    public function __toString() : string
    {
        $genericTypes = [];
        foreach ($this->genericTypes as $index => $type) {
            $variance = $this->variances[$index] ?? self::VARIANCE_INVARIANT;
            if ($variance === self::VARIANCE_INVARIANT) {
                $genericTypes[] = (string) $type;
            } elseif ($variance === self::VARIANCE_BIVARIANT) {
                $genericTypes[] = '*';
            } else {
                $genericTypes[] = sprintf('%s %s', $variance, $type);
            }
        }
        return $this->type . '<' . implode(', ', $genericTypes) . '>';
    }
}
