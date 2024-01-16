<?php

declare (strict_types=1);
namespace Rector\Symfony\SwiftMailer\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\SwiftMailer\Rector\MethodCall\SwiftCreateMessageToNewEmailRector\SwiftCreateMessageToNewEmailRectorTest
 */
final class SwiftCreateMessageToNewEmailRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes createMessage() into a new Symfony\\Component\\Mime\\Email', [new CodeSample(<<<'CODE_SAMPLE'
$email = $this->swift->createMessage('message');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$email = new \Symfony\Component\Mime\Email();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->getMethods(), function (Node $subNode) use($node, &$hasChanged) : ?New_ {
            if (!$subNode instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($subNode->name, 'createMessage')) {
                return null;
            }
            // If there is no property with a SwiftMailer type we should skip this class
            $swiftMailerProperty = $this->getSwiftMailerProperty($node);
            if (!$swiftMailerProperty instanceof Property) {
                return null;
            }
            $var = $subNode->var;
            if (!$var instanceof PropertyFetch) {
                return null;
            }
            $propertyName = $this->getName($swiftMailerProperty);
            if (!$this->isName($var, $propertyName)) {
                return null;
            }
            $hasChanged = \true;
            return new New_(new FullyQualified('Symfony\\Component\\Mime\\Email'));
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function getSwiftMailerProperty(Class_ $class) : ?Property
    {
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $propertyType = $this->nodeTypeResolver->getType($property);
            if (!$propertyType instanceof ObjectType) {
                continue;
            }
            if (!$propertyType->isInstanceOf('Swift_Mailer')->yes()) {
                continue;
            }
            return $property;
        }
        return null;
    }
}
