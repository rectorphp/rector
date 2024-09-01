<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Scalar;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\ScalarValueToConstFetch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202409\Webmozart\Assert\Assert;
/**
 * @see Rector\Tests\Transform\Rector\Scalar\ScalarValueToConstFetchRector\ScalarValueToConstFetchRectorTest
 */
class ScalarValueToConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ScalarValueToConstFetch[]
     */
    private $scalarValueToConstFetches;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces Scalar values with a ConstFetch or ClassConstFetch', [new ConfiguredCodeSample(<<<'SAMPLE'
$var = 10;
SAMPLE
, <<<'SAMPLE'
$var = \SomeClass::FOOBAR_INT;
SAMPLE
, [new ScalarValueToConstFetch(new LNumber(10), new ClassConstFetch(new FullyQualified('SomeClass'), new Identifier('FOOBAR_INT')))])]);
    }
    public function getNodeTypes() : array
    {
        return [String_::class, DNumber::class, LNumber::class];
    }
    /**
     * @param String_|DNumber|LNumber $node
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\ClassConstFetch|null
     */
    public function refactor(Node $node)
    {
        foreach ($this->scalarValueToConstFetches as $scalarValueToConstFetch) {
            if ($node->value === $scalarValueToConstFetch->getScalar()->value) {
                return $scalarValueToConstFetch->getConstFetch();
            }
        }
        return null;
    }
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, ScalarValueToConstFetch::class);
        $this->scalarValueToConstFetches = $configuration;
    }
}
