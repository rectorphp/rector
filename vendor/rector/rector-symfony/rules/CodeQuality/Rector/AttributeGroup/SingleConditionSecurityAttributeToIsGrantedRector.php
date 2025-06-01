<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\AttributeGroup;

use RectorPrefix202506\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Rector\Symfony\CodeQuality\NodeAnalyzer\AttributePresenceDetector;
use Rector\Symfony\Enum\SensioAttribute;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/pull/27305/
 * @see https://stackoverflow.com/a/65439590/1348344
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\AttributeGroup\SingleConditionSecurityAttributeToIsGrantedRector\SingleConditionSecurityAttributeToIsGrantedRectorTest
 */
final class SingleConditionSecurityAttributeToIsGrantedRector extends AbstractRector
{
    /**
     * @readonly
     */
    private AttributePresenceDetector $attributePresenceDetector;
    public function __construct(AttributePresenceDetector $attributePresenceDetector)
    {
        $this->attributePresenceDetector = $attributePresenceDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Narrow #[Security] attribute with inner sigle "is_granted/has_role" condition string to #[IsGranted] attribute', [new CodeSample(<<<'CODE_SAMPLE'
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Security("is_granted('ROLE_USER')")]
class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class SomeClass
{
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [AttributeGroup::class];
    }
    /**
     * @param AttributeGroup $node
     */
    public function refactor(Node $node) : ?AttributeGroup
    {
        if (!$this->attributePresenceDetector->detect(SensioAttribute::SECURITY)) {
            return null;
        }
        foreach ($node->attrs as $attr) {
            if (!$this->isName($attr->name, SensioAttribute::SECURITY)) {
                continue;
            }
            $firstArgValue = $attr->args[0]->value;
            if (!$firstArgValue instanceof String_) {
                continue;
            }
            $matches = Strings::match($firstArgValue->value, '#^(is_granted|has_role)\\(\'(?<access_right>[A-Za-z_]+)\'\\)$#');
            if (!isset($matches['access_right'])) {
                continue;
            }
            $attr->name = new FullyQualified(SensioAttribute::IS_GRANTED);
            $attr->args = [new Arg(new String_($matches['access_right']))];
            return $node;
        }
        return null;
    }
}
