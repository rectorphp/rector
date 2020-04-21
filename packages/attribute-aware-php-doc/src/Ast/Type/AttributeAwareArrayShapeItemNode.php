<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\Type;

use Nette\Utils\Strings;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
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
     * @param ConstExprIntegerNode|ConstExprStringNode|IdentifierTypeNode|null $keyName
     */
    public function __construct($keyName, bool $optional, TypeNode $typeNode, string $docComment = '')
    {
        parent::__construct($keyName, $optional, $typeNode);

        // spaces after double colon
        $keyWithSpacePattern = $this->createKeyWithSpacePattern($keyName, $optional);
        $this->hasSpaceAfterDoubleColon = (bool) Strings::matchAll($docComment, $keyWithSpacePattern);
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

    /**
     * @param ConstExprIntegerNode|IdentifierTypeNode|null $keyName
     */
    private function createKeyWithSpacePattern($keyName, bool $optional): string
    {
        $keyNameString = (string) $keyName;
        if ($optional) {
            $keyNameString .= '?';
        }

        $keyNameStringPregQuoted = preg_quote($keyNameString);

        return sprintf('#%s\:\s+#', $keyNameStringPregQuoted);
    }
}
