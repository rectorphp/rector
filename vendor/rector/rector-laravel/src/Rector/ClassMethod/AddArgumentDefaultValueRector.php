<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\ClassMethod;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Laravel\ValueObject\AddArgumentDefaultValue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Laravel\Tests\Rector\ClassMethod\AddArgumentDefaultValueRector\AddArgumentDefaultValueRectorTest
 */
final class AddArgumentDefaultValueRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ADDED_ARGUMENTS = 'added_arguments';
    /**
     * @var AddArgumentDefaultValue[]
     */
    private $addedArguments = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Adds default value for arguments in defined methods.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function someMethod($value)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function someMethod($value = false)
    {
    }
}
CODE_SAMPLE
, [self::ADDED_ARGUMENTS => [new \Rector\Laravel\ValueObject\AddArgumentDefaultValue('SomeClass', 'someMethod', 0, \false)]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : \PhpParser\Node\Stmt\ClassMethod
    {
        foreach ($this->addedArguments as $addedArgument) {
            if (!$this->nodeTypeResolver->isObjectType($node, $addedArgument->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $addedArgument->getMethod())) {
                continue;
            }
            if (!isset($node->params[$addedArgument->getPosition()])) {
                continue;
            }
            $position = $addedArgument->getPosition();
            $param = $node->params[$position];
            if ($param->default !== null) {
                continue;
            }
            $node->params[$position] = new \PhpParser\Node\Param($param->var, \PhpParser\BuilderHelpers::normalizeValue($addedArgument->getDefaultValue()));
        }
        return $node;
    }
    /**
     * @param array<string, AddArgumentDefaultValue[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $addedArguments = $configuration[self::ADDED_ARGUMENTS] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($addedArguments, \Rector\Laravel\ValueObject\AddArgumentDefaultValue::class);
        $this->addedArguments = $addedArguments;
    }
}
