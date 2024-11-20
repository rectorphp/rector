<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\Type;

use PHPStan\PhpDocParser\Ast\NodeAttributes;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use function implode;
class CallableTypeNode implements \PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    public \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $identifier;
    /** @var TemplateTagValueNode[] */
    public array $templateTypes;
    /** @var CallableTypeParameterNode[] */
    public array $parameters;
    public \PHPStan\PhpDocParser\Ast\Type\TypeNode $returnType;
    /**
     * @param CallableTypeParameterNode[] $parameters
     * @param TemplateTagValueNode[]  $templateTypes
     */
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $identifier, array $parameters, \PHPStan\PhpDocParser\Ast\Type\TypeNode $returnType, array $templateTypes)
    {
        $this->identifier = $identifier;
        $this->parameters = $parameters;
        $this->returnType = $returnType;
        $this->templateTypes = $templateTypes;
    }
    public function __toString() : string
    {
        $returnType = $this->returnType;
        if ($returnType instanceof self) {
            $returnType = "({$returnType})";
        }
        $template = $this->templateTypes !== [] ? '<' . implode(', ', $this->templateTypes) . '>' : '';
        $parameters = implode(', ', $this->parameters);
        return "{$this->identifier}{$template}({$parameters}): {$returnType}";
    }
}
