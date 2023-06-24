<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony62\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Symfony\Enum\SensioAttribute;
use Rector\Symfony\Enum\SymfonyAnnotation;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
 *
 * @see \Rector\Symfony\Tests\Symfony62\Rector\ClassMethod\ParamConverterAttributeToMapEntityAttributeRector\ParamConverterAttributeToMapEntityAttributeRectorTest
 */
final class ParamConverterAttributeToMapEntityAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace ParamConverter attribute with mappings with the MapEntity attribute', [new CodeSample(<<<'CODE_SAMPLE'
class SomeController
{
    #[ParamConverter('post', options: ['mapping' => ['date' => 'date', 'slug' => 'slug']])]
    #[ParamConverter('comment', options: ['mapping' => ['comment_slug' => 'slug']])]
    public function showComment(
        Post $post,

        Comment $comment
    ) {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

class SomeController
{
    public function showComment(
        #[MapEntity(mapping: ['date' => 'date', 'slug' => 'slug'])]
        Post $post,

        #[MapEntity(mapping: ['comment_slug' => 'slug'])]
        Comment $comment
    ) {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->isPublic()) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->attrGroups as $attrGroupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if (!$this->isNames($attr, [SensioAttribute::PARAM_CONVERTER, SensioAttribute::ENTITY])) {
                    continue;
                }
                $attribute = $this->refactorAttribute($node, $attr, $attrGroup);
                if ($attribute instanceof Attribute) {
                    unset($node->attrGroups[$attrGroupKey]);
                    $hasChanged = \true;
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function refactorAttribute(ClassMethod $classMethod, Attribute $attribute, AttributeGroup $attributeGroup) : ?Attribute
    {
        $firstArg = $attribute->args[0] ?? null;
        if (!$firstArg instanceof Arg) {
            return null;
        }
        if (!$firstArg->value instanceof String_) {
            return null;
        }
        $optionsIndex = $this->getIndexForOptionsArg($attribute->args);
        $exprIndex = $this->getIndexForExprArg($attribute->args);
        if (!$optionsIndex && !$exprIndex) {
            return null;
        }
        $name = $firstArg->value->value;
        $mappingArg = $attribute->args[$optionsIndex] ?? null;
        $mappingExpr = $mappingArg instanceof Arg ? $mappingArg->value : null;
        $exprArg = $attribute->args[$exprIndex] ?? null;
        $exprValue = $exprArg instanceof Arg ? $exprArg->value : null;
        $newArguments = $this->getNewArguments($mappingExpr, $exprValue);
        if ($newArguments === []) {
            return null;
        }
        unset($attribute->args[0]);
        if ($optionsIndex) {
            unset($attribute->args[$optionsIndex]);
        }
        if ($exprIndex) {
            unset($attribute->args[$exprIndex]);
        }
        $attribute->args = \array_merge($attribute->args, $newArguments);
        $attribute->name = new FullyQualified(SymfonyAnnotation::MAP_ENTITY);
        $this->addMapEntityAttribute($classMethod, $name, $attributeGroup);
        return $attribute;
    }
    /**
     * @return Arg[]
     */
    private function getNewArguments(?Expr $mapping, ?Expr $exprValue) : array
    {
        $newArguments = [];
        if ($mapping instanceof Array_) {
            $probablyEntity = \false;
            foreach ($mapping->items as $item) {
                if (!$item instanceof ArrayItem || !$item->key instanceof String_) {
                    continue;
                }
                if (\in_array($item->key->value, ['mapping', 'entity_manager'], \true)) {
                    $probablyEntity = \true;
                }
                $newArguments[] = new Arg($item->value, \false, \false, [], new Identifier($item->key->value));
            }
            if (!$probablyEntity) {
                return [];
            }
        }
        if ($exprValue instanceof String_) {
            $newArguments[] = new Arg($exprValue, \false, \false, [], new Identifier('expr'));
        }
        return $newArguments;
    }
    private function addMapEntityAttribute(ClassMethod $classMethod, string $variableName, AttributeGroup $attributeGroup) : void
    {
        foreach ($classMethod->params as $param) {
            if (!$param->var instanceof Variable) {
                continue;
            }
            if (!$this->isName($param->var, $variableName)) {
                continue;
            }
            $param->attrGroups = [$attributeGroup];
        }
    }
    /**
     * @param Arg[] $args
     */
    private function getIndexForOptionsArg(array $args) : ?int
    {
        foreach ($args as $key => $arg) {
            if ($arg->name instanceof Identifier && $arg->name->name === 'options') {
                return $key;
            }
        }
        return null;
    }
    /**
     * @param Arg[] $args
     */
    private function getIndexForExprArg(array $args) : ?int
    {
        foreach ($args as $key => $arg) {
            if ($arg->name instanceof Identifier && $arg->name->name === 'expr') {
                return $key;
            }
        }
        return null;
    }
}
