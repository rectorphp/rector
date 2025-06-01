<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use RectorPrefix202506\Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use RectorPrefix202506\Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class SplitAndSecurityAttributeToIsGrantedRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Split #[Security] attribute with "and" condition string to multiple #[IsGranted] attributes with sole values', [new CodeSample(<<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Security("is_granted('ROLE_USER') and has_role('ROLE_ADMIN')")]
class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[IsGranted('ROLE_ADMIN')]
class SomeClass
{
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class, ClassMethod::class];
    }
    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->attrGroups as $key => $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if (!$this->isName($attr->name, Security::class)) {
                    continue;
                }
                $firstArgValue = $attr->args[0]->value;
                if (!$firstArgValue instanceof String_) {
                    continue;
                }
                $content = $firstArgValue->value;
                // unable to resolve with pure attributes
                if (\strpos($content, ' or ') !== \false) {
                    continue;
                }
                // we look for "and"s
                if (\strpos($content, ' and ') === \false && \strpos($content, ' && ') === \false) {
                    continue;
                }
                // split by && and "and"
                $andItems = \strpos($content, ' && ') !== \false ? \explode(' && ', $content) : \explode(' and ', $content);
                $accessRights = [];
                foreach ($andItems as $andItem) {
                    $matches = Strings::match($andItem, '#^(is_granted|has_role)\\(\'(?<access_right>[A-Za-z_]+)\'\\)$#');
                    if (!isset($matches['access_right'])) {
                        // all or nothing
                        return null;
                    }
                    $accessRights[] = $matches['access_right'];
                }
                unset($node->attrGroups[$key]);
                $hasChanged = \true;
                foreach ($accessRights as $accessRight) {
                    $attributeGroup = new AttributeGroup([new Attribute(new FullyQualified(IsGranted::class), [new Arg(new String_($accessRight))])]);
                    $node->attrGroups[] = $attributeGroup;
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
