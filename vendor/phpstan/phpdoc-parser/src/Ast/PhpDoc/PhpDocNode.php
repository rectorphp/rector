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
    public function getTagsByName(string $tagName) : array
    {
        return \array_filter($this->getTags(), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) use($tagName) : bool {
            return $tag->name === $tagName;
        });
    }
    /**
     * @return VarTagValueNode[]
     */
    public function getVarTagValues(string $tagName = '@var') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
        }), 'value');
    }
    /**
     * @return ParamTagValueNode[]
     */
    public function getParamTagValues(string $tagName = '@param') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
        }), 'value');
    }
    /**
     * @return TemplateTagValueNode[]
     */
    public function getTemplateTagValues(string $tagName = '@template') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
        }), 'value');
    }
    /**
     * @return ExtendsTagValueNode[]
     */
    public function getExtendsTagValues(string $tagName = '@extends') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ExtendsTagValueNode;
        }), 'value');
    }
    /**
     * @return ImplementsTagValueNode[]
     */
    public function getImplementsTagValues(string $tagName = '@implements') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ImplementsTagValueNode;
        }), 'value');
    }
    /**
     * @return UsesTagValueNode[]
     */
    public function getUsesTagValues(string $tagName = '@use') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode;
        }), 'value');
    }
    /**
     * @return ReturnTagValueNode[]
     */
    public function getReturnTagValues(string $tagName = '@return') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
        }), 'value');
    }
    /**
     * @return ThrowsTagValueNode[]
     */
    public function getThrowsTagValues(string $tagName = '@throws') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
        }), 'value');
    }
    /**
     * @return MixinTagValueNode[]
     */
    public function getMixinTagValues(string $tagName = '@mixin') : array
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
     */
    public function getPropertyTagValues(string $tagName = '@property') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        }), 'value');
    }
    /**
     * @return PropertyTagValueNode[]
     */
    public function getPropertyReadTagValues(string $tagName = '@property-read') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        }), 'value');
    }
    /**
     * @return PropertyTagValueNode[]
     */
    public function getPropertyWriteTagValues(string $tagName = '@property-write') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        }), 'value');
    }
    /**
     * @return MethodTagValueNode[]
     */
    public function getMethodTagValues(string $tagName = '@method') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
        }), 'value');
    }
    /**
     * @return TypeAliasTagValueNode[]
     */
    public function getTypeAliasTagValues(string $tagName = '@phpstan-type') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
        }), 'value');
    }
    /**
     * @return TypeAliasImportTagValueNode[]
     */
    public function getTypeAliasImportTagValues(string $tagName = '@phpstan-import-type') : array
    {
        return \array_column(\array_filter($this->getTagsByName($tagName), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) : bool {
            return $tag->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasImportTagValueNode;
        }), 'value');
    }
    public function __toString() : string
    {
        $children = \array_map(static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode $child) : string {
            $s = (string) $child;
            return $s === '' ? '' : ' ' . $s;
        }, $this->children);
        return "/**\n *" . \implode("\n *", $children) . "\n */";
    }
}
