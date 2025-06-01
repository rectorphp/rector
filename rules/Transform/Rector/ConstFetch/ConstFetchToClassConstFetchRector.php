<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\ConstFetchToClassConstFetch;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202506\Webmozart\Assert\Assert;
/**
 * @see Rector\Tests\Transform\Rector\ConstFetch\ConstFetchToClassConstFetchRector\ConstFetchToClassConstFetchTest
 */
final class ConstFetchToClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ConstFetchToClassConstFetch[]
     */
    private array $constFetchToClassConsts = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change const fetch to class const fetch', [new ConfiguredCodeSample('$x = CONTEXT_COURSE', '$x = course::LEVEL', [new ConstFetchToClassConstFetch('CONTEXT_COURSE', 'course', 'LEVEL')])]);
    }
    public function getNodeTypes() : array
    {
        return [ConstFetch::class];
    }
    public function refactor(Node $node) : ?ClassConstFetch
    {
        foreach ($this->constFetchToClassConsts as $constFetchToClassConst) {
            if (!$this->isName($node, $constFetchToClassConst->getOldConstName())) {
                continue;
            }
            return $this->nodeFactory->createClassConstFetch($constFetchToClassConst->getNewClassName(), $constFetchToClassConst->getNewConstName());
        }
        return null;
    }
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, ConstFetchToClassConstFetch::class);
        $this->constFetchToClassConsts = $configuration;
    }
}
