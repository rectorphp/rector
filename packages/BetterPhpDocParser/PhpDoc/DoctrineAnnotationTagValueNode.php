<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDoc;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\AbstractValuesAwareNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Stringable;
final class DoctrineAnnotationTagValueNode extends \Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\AbstractValuesAwareNode
{
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode
     */
    public $identifierTypeNode;
    /**
     * @param array<mixed, mixed> $values
     */
    public function __construct(\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $identifierTypeNode, ?string $originalContent = null, array $values = [], ?string $silentKey = null)
    {
        $this->identifierTypeNode = $identifierTypeNode;
        $this->hasChanged = \true;
        parent::__construct($values, $originalContent, $silentKey);
    }
    public function __toString() : string
    {
        if (!$this->hasChanged) {
            if ($this->originalContent === null) {
                return '';
            }
            return $this->originalContent;
        }
        if ($this->values === []) {
            if ($this->originalContent === '()') {
                // empty brackets
                return $this->originalContent;
            }
            return '';
        }
        $itemContents = $this->printValuesContent($this->values);
        return \sprintf('(%s)', $itemContents);
    }
    /**
     * @param string[] $classNames
     */
    public function hasClassNames(array $classNames) : bool
    {
        foreach ($classNames as $className) {
            if ($this->hasClassName($className)) {
                return \true;
            }
        }
        return \false;
    }
    public function hasClassName(string $className) : bool
    {
        $annotationName = \trim($this->identifierTypeNode->name, '@');
        if ($annotationName === $className) {
            return \true;
        }
        // the name is not fully qualified in the original name, look for resolvd class attirubte
        $resolvedClass = $this->identifierTypeNode->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::RESOLVED_CLASS);
        return $resolvedClass === $className;
    }
}
