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
use RectorPrefix202208\Webmozart\Assert\Assert;
/**
 * @see \Rector\Laravel\Tests\Rector\ClassMethod\AddArgumentDefaultValueRector\AddArgumentDefaultValueRectorTest
 */
final class AddArgumentDefaultValueRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ADDED_ARGUMENTS = 'added_arguments';
    /**
     * @var AddArgumentDefaultValue[]
     */
    private $addedArguments = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds default value for arguments in defined methods.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [self::ADDED_ARGUMENTS => [new AddArgumentDefaultValue('SomeClass', 'someMethod', 0, \false)]])]);
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
    public function refactor(Node $node) : ClassMethod
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
            $node->params[$position] = new Param($param->var, BuilderHelpers::normalizeValue($addedArgument->getDefaultValue()));
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $addedArguments = $configuration[self::ADDED_ARGUMENTS] ?? $configuration;
        Assert::isArray($addedArguments);
        Assert::allIsInstanceOf($addedArguments, AddArgumentDefaultValue::class);
        $this->addedArguments = $addedArguments;
    }
}
