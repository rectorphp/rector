<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\Transform\ValueObject\ConstFetchToClassConstFetch;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @see Rector\Tests\Transform\Rector\ConstFetch\ConstFetchToClassConstFetchRector\ConstFetchToClassConstFetchTest
 */
final class ConstFetchToClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, ConstFetchToClassConstFetch[]>
     */
    private array $constFetchToClassConstsByOldName = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change const fetch to class const fetch', [new ConfiguredCodeSample('$x = CONTEXT_COURSE', '$x = course::LEVEL', [new ConstFetchToClassConstFetch('CONTEXT_COURSE', 'course', 'LEVEL')])]);
    }
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }
    /**
     * @param ConstFetch $node
     */
    public function refactor(Node $node): ?ClassConstFetch
    {
        $constName = $this->getName($node);
        if ($constName === null) {
            return null;
        }
        foreach ($this->constFetchToClassConstsByOldName[strtolower($constName)] ?? [] as $constFetchToClassConst) {
            return $this->nodeFactory->createClassConstFetch($constFetchToClassConst->getNewClassName(), $constFetchToClassConst->getNewConstName());
        }
        return null;
    }
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, ConstFetchToClassConstFetch::class);
        $this->constFetchToClassConstsByOldName = [];
        foreach ($configuration as $constFetchToClassConst) {
            $this->constFetchToClassConstsByOldName[strtolower($constFetchToClassConst->getOldConstName())][] = $constFetchToClassConst;
        }
    }
}
