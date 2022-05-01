<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\VariadicPlaceholder;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\ClassMethod\ArgumentRemoverRector\ArgumentRemoverRectorTest
 */
final class ArgumentRemoverRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var ArgumentRemover[]
     */
    private $removedArguments = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes defined arguments in defined methods and their calls.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(true);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod();
CODE_SAMPLE
, [new \Rector\Removing\ValueObject\ArgumentRemover('ExampleClass', 'someMethod', 0, [\true])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod
     */
    public function refactor(\PhpParser\Node $node)
    {
        foreach ($this->removedArguments as $removedArgument) {
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, $removedArgument->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $removedArgument->getMethod())) {
                continue;
            }
            $this->processPosition($node, $removedArgument);
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Removing\ValueObject\ArgumentRemover::class);
        $this->removedArguments = $configuration;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $node
     */
    private function processPosition($node, \Rector\Removing\ValueObject\ArgumentRemover $argumentRemover) : void
    {
        if ($argumentRemover->getValue() === null) {
            if ($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\StaticCall) {
                $this->nodeRemover->removeArg($node, $argumentRemover->getPosition());
            } else {
                $this->nodeRemover->removeParam($node, $argumentRemover->getPosition());
            }
            return;
        }
        $match = $argumentRemover->getValue();
        if (isset($match['name'])) {
            $this->removeByName($node, $argumentRemover->getPosition(), $match['name']);
            return;
        }
        // only argument specific value can be removed
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return;
        }
        if (!isset($node->args[$argumentRemover->getPosition()])) {
            return;
        }
        if ($this->isArgumentValueMatch($node->args[$argumentRemover->getPosition()], $match)) {
            $this->nodeRemover->removeArg($node, $argumentRemover->getPosition());
        }
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $node
     */
    private function removeByName($node, int $position, string $name) : void
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall || $node instanceof \PhpParser\Node\Expr\StaticCall) {
            if (isset($node->args[$position]) && $this->isName($node->args[$position], $name)) {
                $this->nodeRemover->removeArg($node, $position);
            }
            return;
        }
        if (!(isset($node->params[$position]) && $this->isName($node->params[$position], $name))) {
            return;
        }
        $this->nodeRemover->removeParam($node, $position);
    }
    /**
     * @param mixed[] $values
     * @param \PhpParser\Node\Arg|\PhpParser\Node\VariadicPlaceholder $arg
     */
    private function isArgumentValueMatch($arg, array $values) : bool
    {
        if (!$arg instanceof \PhpParser\Node\Arg) {
            return \false;
        }
        $nodeValue = $this->valueResolver->getValue($arg->value);
        return \in_array($nodeValue, $values, \true);
    }
}
