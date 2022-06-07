<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\SwiftCreateMessageToNewEmailRector\SwiftCreateMessageToNewEmailRectorTest
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        return new New_(new FullyQualified('Symfony\\Component\\Mime\\Email'));
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'createMessage')) {
            return \true;
        }
        // If there is no property with a SwiftMailer type we should skip this class
        $swiftMailerProperty = $this->getSwiftMailerProperty($methodCall);
        if (!$swiftMailerProperty instanceof Property) {
            return \true;
        }
        $var = $methodCall->var;
        if (!$var instanceof PropertyFetch) {
            return \true;
        }
        $propertyName = $this->getName($swiftMailerProperty);
        return !$this->isName($var, $propertyName);
    }
    private function getSwiftMailerProperty(MethodCall $methodCall) : ?Property
    {
        $class = $this->betterNodeFinder->findParentType($methodCall, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
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
