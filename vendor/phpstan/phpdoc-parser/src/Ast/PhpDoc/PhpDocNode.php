<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
class PhpDocNode implements \PHPStan\PhpDocParser\Ast\Node
{
    use NodeAttributes;
    /** @var PhpDocChildNode[] */
    public $children;
    /**
     * @param PhpDocChildNode[] $children
     */
    public function __construct(array $children)
    {
        $this->children = $children;
    }
    /**
     * @return PhpDocTagNode[]
     */
    public function getTags() : array
    {
        return \array_filter($this->children, static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode $child) : bool {
            return $child instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
        });
    }
    /**
     * @param  string $tagName
     * @return PhpDocTagNode[]
     */
    public function getTagsByName($tagName) : array
    {
        return \array_filter($this->getTags(), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) use($tagName) : bool {
            return $tag->name === $tagName;
        });
    }
    /**
     * @return VarTagValueNode[]
     * @param string $tagName
     */
    public function getVarTagValues($tagName = '@var') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
        }), 'value');
    }
    /**
     * @return ParamTagValueNode[]
     * @param string $tagName
     */
    public function getParamTagValues($tagName = '@param') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
        }), 'value');
    }
    /**
     * @return TemplateTagValueNode[]
     * @param string $tagName
     */
    public function getTemplateTagValues($tagName = '@template') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
        }), 'value');
    }
    /**
     * @return ExtendsTagValueNode[]
     * @param string $tagName
     */
    public function getExtendsTagValues($tagName = '@extends') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ExtendsTagValueNode;
        }), 'value');
    }
    /**
     * @return ImplementsTagValueNode[]
     * @param string $tagName
     */
    public function getImplementsTagValues($tagName = '@implements') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ImplementsTagValueNode;
        }), 'value');
    }
    /**
     * @return UsesTagValueNode[]
     * @param string $tagName
     */
    public function getUsesTagValues($tagName = '@use') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode;
        }), 'value');
    }
    /**
     * @return ReturnTagValueNode[]
     * @param string $tagName
     */
    public function getReturnTagValues($tagName = '@return') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
        }), 'value');
    }
    /**
     * @return ThrowsTagValueNode[]
     * @param string $tagName
     */
    public function getThrowsTagValues($tagName = '@throws') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
        }), 'value');
    }
    /**
     * @return MixinTagValueNode[]
     * @param string $tagName
     */
    public function getMixinTagValues($tagName = '@mixin') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\MixinTagValueNode;
        }), 'value');
    }
    /**
     * @return \PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode[]
     */
    public function getDeprecatedTagValues() : array
    {
        return \array_column(\array_filter($this->getTagsByName('@deprecated'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
        }), 'value');
    }
    /**
     * @return PropertyTagValueNode[]
     * @param string $tagName
     */
    public function getPropertyTagValues($tagName = '@property') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        }), 'value');
    }
    /**
     * @return PropertyTagValueNode[]
     * @param string $tagName
     */
    public function getPropertyReadTagValues($tagName = '@property-read') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        }), 'value');
    }
    /**
     * @return PropertyTagValueNode[]
     * @param string $tagName
     */
    public function getPropertyWriteTagValues($tagName = '@property-write') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        }), 'value');
    }
    /**
     * @return MethodTagValueNode[]
     * @param string $tagName
     */
    public function getMethodTagValues($tagName = '@method') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
        }), 'value');
    }
    /**
     * @return TypeAliasTagValueNode[]
     * @param string $tagName
     */
    public function getTypeAliasTagValues($tagName = '@phpstan-type') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
        }), 'value');
    }
    /**
     * @return TypeAliasImportTagValueNode[]
     * @param string $tagName
     */
    public function getTypeAliasImportTagValues($tagName = '@phpstan-import-type') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasImportTagValueNode;
        }), 'value');
    }
    public function __toString() : string
    {
        return "/**\n * " . \implode("\n * ", $this->children) . "\n */";
    }
}
