<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareArrayShapeItemNode extends ArrayShapeItemNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * @var bool
     */
    private $hasSpaceAfterDoubleColon = false;

    /**
     * @param ConstExprIntegerNode|IdentifierTypeNode|null $keyName
     */
    public function __construct($keyName, bool $optional, TypeNode $typeNode, string $docComment = '')
    {
        parent::__construct($keyName, $optional, $typeNode);

        $this->hasSpaceAfterDoubleColon = (bool) Strings::match($docComment, '#\:\s+#');
    }

    public function __toString(): string
    {
        if ($this->keyName === null) {
            return (string) $this->valueType;
        }

        return sprintf(
            '%s%s:%s%s',
            (string) $this->keyName,
            $this->optional ? '?' : '',
            $this->hasSpaceAfterDoubleColon ? ' ' : '',
            (string) $this->valueType
        );
    }
}
