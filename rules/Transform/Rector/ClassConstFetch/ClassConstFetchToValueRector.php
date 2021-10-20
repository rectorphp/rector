<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ClassConstFetch;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\ClassConstFetchToValue;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\ClassConstFetch\ClassConstFetchToValueRector\ClassConstFetchToValueRectorTest
 */
final class ClassConstFetchToValueRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const CLASS_CONST_FETCHES_TO_VALUES = 'old_constants_to_new_valuesByType';
    /**
     * @var ClassConstFetchToValue[]
     */
    private $classConstFetchesToValues = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $configuration = [self::CLASS_CONST_FETCHES_TO_VALUES => [new \Rector\Transform\ValueObject\ClassConstFetchToValue('Nette\\Configurator', 'DEVELOPMENT', 'development'), new \Rector\Transform\ValueObject\ClassConstFetchToValue('Nette\\Configurator', 'PRODUCTION', 'production')]];
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces constant by value', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample('$value === Nette\\Configurator::DEVELOPMENT', '$value === "development"', $configuration)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->classConstFetchesToValues as $classConstFetchToValue) {
            if (!$this->isObjectType($node->class, $classConstFetchToValue->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $classConstFetchToValue->getConstant())) {
                continue;
            }
            return \PhpParser\BuilderHelpers::normalizeValue($classConstFetchToValue->getValue());
        }
        return $node;
    }
    /**
     * @param array<string, ClassConstFetchToValue[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $classConstFetchesToValues = $configuration[self::CLASS_CONST_FETCHES_TO_VALUES] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($classConstFetchesToValues, \Rector\Transform\ValueObject\ClassConstFetchToValue::class);
        $this->classConstFetchesToValues = $classConstFetchesToValues;
    }
}
