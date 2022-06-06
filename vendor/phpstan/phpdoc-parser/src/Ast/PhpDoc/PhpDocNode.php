<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Node;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function array_column;
use function array_filter;
use function array_map;
use function implode;
class PhpDocNode implements Node
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
        return array_filter($this->children, static function (PhpDocChildNode $child) : bool {
            return $child instanceof PhpDocTagNode;
        });
    }
    /**
     * @return PhpDocTagNode[]
     */
    public function getTagsByName(string $tagName) : array
    {
        return array_filter($this->getTags(), static function (PhpDocTagNode $tag) use($tagName) : bool {
            return $tag->name === $tagName;
        });
    }
    /**
     * @return VarTagValueNode[]
     */
    public function getVarTagValues(string $tagName = '@var') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof VarTagValueNode;
        });
    }
    /**
     * @return ParamTagValueNode[]
     */
    public function getParamTagValues(string $tagName = '@param') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof ParamTagValueNode;
        });
    }
    /**
     * @return TemplateTagValueNode[]
     */
    public function getTemplateTagValues(string $tagName = '@template') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof TemplateTagValueNode;
        });
    }
    /**
     * @return ExtendsTagValueNode[]
     */
    public function getExtendsTagValues(string $tagName = '@extends') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof ExtendsTagValueNode;
        });
    }
    /**
     * @return ImplementsTagValueNode[]
     */
    public function getImplementsTagValues(string $tagName = '@implements') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof ImplementsTagValueNode;
        });
    }
    /**
     * @return UsesTagValueNode[]
     */
    public function getUsesTagValues(string $tagName = '@use') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof UsesTagValueNode;
        });
    }
    /**
     * @return ReturnTagValueNode[]
     */
    public function getReturnTagValues(string $tagName = '@return') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof ReturnTagValueNode;
        });
    }
    /**
     * @return ThrowsTagValueNode[]
     */
    public function getThrowsTagValues(string $tagName = '@throws') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof ThrowsTagValueNode;
        });
    }
    /**
     * @return MixinTagValueNode[]
     */
    public function getMixinTagValues(string $tagName = '@mixin') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof MixinTagValueNode;
        });
    }
    /**
     * @return DeprecatedTagValueNode[]
     */
    public function getDeprecatedTagValues() : array
    {
        return array_filter(array_column($this->getTagsByName('@deprecated'), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof DeprecatedTagValueNode;
        });
    }
    /**
     * @return PropertyTagValueNode[]
     */
    public function getPropertyTagValues(string $tagName = '@property') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof PropertyTagValueNode;
        });
    }
    /**
     * @return PropertyTagValueNode[]
     */
    public function getPropertyReadTagValues(string $tagName = '@property-read') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof PropertyTagValueNode;
        });
    }
    /**
     * @return PropertyTagValueNode[]
     */
    public function getPropertyWriteTagValues(string $tagName = '@property-write') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof PropertyTagValueNode;
        });
    }
    /**
     * @return MethodTagValueNode[]
     */
    public function getMethodTagValues(string $tagName = '@method') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof MethodTagValueNode;
        });
    }
    /**
     * @return TypeAliasTagValueNode[]
     */
    public function getTypeAliasTagValues(string $tagName = '@phpstan-type') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof TypeAliasTagValueNode;
        });
    }
    /**
     * @return TypeAliasImportTagValueNode[]
     */
    public function getTypeAliasImportTagValues(string $tagName = '@phpstan-import-type') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof TypeAliasImportTagValueNode;
        });
    }
    /**
     * @return AssertTagValueNode[]
     */
    public function getAssertTagValues(string $tagName = '@phpstan-assert') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (PhpDocTagValueNode $value) : bool {
            return $value instanceof AssertTagValueNode;
        });
    }
    public function __toString() : string
    {
        $children = array_map(static function (PhpDocChildNode $child) : string {
            $s = (string) $child;
            return $s === '' ? '' : ' ' . $s;
        }, $this->children);
        return "/**\n *" . implode("\n *", $children) . "\n */";
    }
}
