<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\TypeAwareTagValueNodeInterface;

final class AttributeAwareParamTagValueNode extends ParamTagValueNode implements AttributeAwareNodeInterface, TypeAwareTagValueNodeInterface
{
    use AttributeTrait;

    /**
     * @var bool
     */
    private $isReference = false;

    /**
     * The constructor override is needed to add support for reference &
     * @see https://github.com/rectorphp/rector/issues/1734
     */
    public function __construct(
        TypeNode $typeNode,
        bool $isVariadic,
        string $parameterName,
        string $description,
        bool $isReference
    ) {
        parent::__construct($typeNode, $isVariadic, $parameterName, $description);

        $this->isReference = $isReference;
    }

    public function __toString(): string
    {
        $variadic = $this->isVariadic ? '...' : '';
        $reference = $this->isReference ? '&' : '';

        $content = sprintf(
            '%s %s%s%s %s',
            $this->type,
            $variadic,
            $reference,
            $this->parameterName,
            $this->description
        );

        return trim($content);
    }

    public function isReference(): bool
    {
        return $this->isReference;
    }
}
