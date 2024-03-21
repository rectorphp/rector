<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\NodeAttributes;
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
        return array_filter($this->children, static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode $child) : bool {
            return $child instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
        });
    }
    /**
     * @return PhpDocTagNode[]
     */
    public function getTagsByName(string $tagName) : array
    {
        return array_filter($this->getTags(), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $tag) use($tagName) : bool {
            return $tag->name === $tagName;
        });
    }
    /**
     * @return VarTagValueNode[]
     */
    public function getVarTagValues(string $tagName = '@var') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
        });
    }
    /**
     * @return ParamTagValueNode[]
     */
    public function getParamTagValues(string $tagName = '@param') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
        });
    }
    /**
     * @return TypelessParamTagValueNode[]
     */
    public function getTypelessParamTagValues(string $tagName = '@param') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TypelessParamTagValueNode;
        });
    }
    /**
     * @return ParamImmediatelyInvokedCallableTagValueNode[]
     */
    public function getParamImmediatelyInvokedCallableTagValues(string $tagName = '@param-immediately-invoked-callable') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamImmediatelyInvokedCallableTagValueNode;
        });
    }
    /**
     * @return ParamLaterInvokedCallableTagValueNode[]
     */
    public function getParamLaterInvokedCallableTagValues(string $tagName = '@param-later-invoked-callable') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamLaterInvokedCallableTagValueNode;
        });
    }
    /**
     * @return ParamClosureThisTagValueNode[]
     */
    public function getParamClosureThisTagValues(string $tagName = '@param-closure-this') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamClosureThisTagValueNode;
        });
    }
    /**
     * @return TemplateTagValueNode[]
     */
    public function getTemplateTagValues(string $tagName = '@template') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
        });
    }
    /**
     * @return ExtendsTagValueNode[]
     */
    public function getExtendsTagValues(string $tagName = '@extends') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ExtendsTagValueNode;
        });
    }
    /**
     * @return ImplementsTagValueNode[]
     */
    public function getImplementsTagValues(string $tagName = '@implements') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ImplementsTagValueNode;
        });
    }
    /**
     * @return UsesTagValueNode[]
     */
    public function getUsesTagValues(string $tagName = '@use') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode;
        });
    }
    /**
     * @return ReturnTagValueNode[]
     */
    public function getReturnTagValues(string $tagName = '@return') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
        });
    }
    /**
     * @return ThrowsTagValueNode[]
     */
    public function getThrowsTagValues(string $tagName = '@throws') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
        });
    }
    /**
     * @return MixinTagValueNode[]
     */
    public function getMixinTagValues(string $tagName = '@mixin') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\MixinTagValueNode;
        });
    }
    /**
     * @return RequireExtendsTagValueNode[]
     */
    public function getRequireExtendsTagValues(string $tagName = '@phpstan-require-extends') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\RequireExtendsTagValueNode;
        });
    }
    /**
     * @return RequireImplementsTagValueNode[]
     */
    public function getRequireImplementsTagValues(string $tagName = '@phpstan-require-implements') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\RequireImplementsTagValueNode;
        });
    }
    /**
     * @return DeprecatedTagValueNode[]
     */
    public function getDeprecatedTagValues() : array
    {
        return array_filter(array_column($this->getTagsByName('@deprecated'), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\DeprecatedTagValueNode;
        });
    }
    /**
     * @return PropertyTagValueNode[]
     */
    public function getPropertyTagValues(string $tagName = '@property') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        });
    }
    /**
     * @return PropertyTagValueNode[]
     */
    public function getPropertyReadTagValues(string $tagName = '@property-read') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        });
    }
    /**
     * @return PropertyTagValueNode[]
     */
    public function getPropertyWriteTagValues(string $tagName = '@property-write') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
        });
    }
    /**
     * @return MethodTagValueNode[]
     */
    public function getMethodTagValues(string $tagName = '@method') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
        });
    }
    /**
     * @return TypeAliasTagValueNode[]
     */
    public function getTypeAliasTagValues(string $tagName = '@phpstan-type') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
        });
    }
    /**
     * @return TypeAliasImportTagValueNode[]
     */
    public function getTypeAliasImportTagValues(string $tagName = '@phpstan-import-type') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasImportTagValueNode;
        });
    }
    /**
     * @return AssertTagValueNode[]
     */
    public function getAssertTagValues(string $tagName = '@phpstan-assert') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\AssertTagValueNode;
        });
    }
    /**
     * @return AssertTagPropertyValueNode[]
     */
    public function getAssertPropertyTagValues(string $tagName = '@phpstan-assert') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\AssertTagPropertyValueNode;
        });
    }
    /**
     * @return AssertTagMethodValueNode[]
     */
    public function getAssertMethodTagValues(string $tagName = '@phpstan-assert') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\AssertTagMethodValueNode;
        });
    }
    /**
     * @return SelfOutTagValueNode[]
     */
    public function getSelfOutTypeTagValues(string $tagName = '@phpstan-this-out') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\SelfOutTagValueNode;
        });
    }
    /**
     * @return ParamOutTagValueNode[]
     */
    public function getParamOutTypeTagValues(string $tagName = '@param-out') : array
    {
        return array_filter(array_column($this->getTagsByName($tagName), 'value'), static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value) : bool {
            return $value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\ParamOutTagValueNode;
        });
    }
    public function __toString() : string
    {
        $children = array_map(static function (\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode $child) : string {
            $s = (string) $child;
            return $s === '' ? '' : ' ' . $s;
        }, $this->children);
        return "/**\n *" . implode("\n *", $children) . "\n */";
    }
}
