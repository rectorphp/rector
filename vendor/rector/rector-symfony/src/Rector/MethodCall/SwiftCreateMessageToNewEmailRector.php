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
final class SwiftCreateMessageToNewEmailRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes createMessage() into a new Symfony\\Component\\Mime\\Email', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Mime\\Email'));
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'createMessage')) {
            return \true;
        }
        // If there is no property with a SwiftMailer type we should skip this class
        $swiftMailerProperty = $this->getSwiftMailerProperty($methodCall);
        if (!$swiftMailerProperty instanceof \PhpParser\Node\Stmt\Property) {
            return \true;
        }
        $var = $methodCall->var;
        if (!$var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \true;
        }
        $propertyName = $this->getName($swiftMailerProperty);
        return !$this->isName($var, $propertyName);
    }
    private function getSwiftMailerProperty(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node\Stmt\Property
    {
        $class = $this->betterNodeFinder->findParentType($methodCall, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $propertyType = $this->nodeTypeResolver->getType($property);
            if (!$propertyType instanceof \PHPStan\Type\ObjectType) {
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
