<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\String_\ToStringToMethodCallRector\ToStringToMethodCallRectorTest
 */
final class ToStringToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private $methodNamesByType = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns defined code uses of "__toString()" method  to specific method calls.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someValue = new SomeObject;
$result = (string) $someValue;
$result = $someValue->__toString();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someValue = new SomeObject;
$result = $someValue->getPath();
$result = $someValue->getPath();
CODE_SAMPLE
, ['SomeObject' => 'getPath'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [String_::class, MethodCall::class];
    }
    /**
     * @param String_|MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof String_) {
            return $this->processStringNode($node);
        }
        return $this->processMethodCall($node);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString(\array_keys($configuration));
        Assert::allString($configuration);
        /** @var array<string, string> $configuration */
        $this->methodNamesByType = $configuration;
    }
    private function processStringNode(String_ $string) : ?Node
    {
        foreach ($this->methodNamesByType as $type => $methodName) {
            if (!$this->isObjectType($string->expr, new ObjectType($type))) {
                continue;
            }
            return $this->nodeFactory->createMethodCall($string->expr, $methodName);
        }
        return null;
    }
    private function processMethodCall(MethodCall $methodCall) : ?Node
    {
        foreach ($this->methodNamesByType as $type => $methodName) {
            if (!$this->isObjectType($methodCall->var, new ObjectType($type))) {
                continue;
            }
            if (!$this->isName($methodCall->name, '__toString')) {
                continue;
            }
            $methodCall->name = new Identifier($methodName);
            return $methodCall;
        }
        return null;
    }
}
