<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Rector\RuleDoc\CodeSample\ConfiguredCodeSample;
use Rector\RuleDoc\RuleDefinition;
use RectorPrefix202610\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\StaticCall\RenameStaticMethodRector\RenameStaticMethodRectorTest
 */
final class RenameStaticMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, RenameStaticMethod[]>
     */
    private array $staticMethodRenamesByOldMethod = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn method names to new ones', [new ConfiguredCodeSample('SomeClass::oldStaticMethod();', 'AnotherExampleClass::newStaticMethod();', [new RenameStaticMethod('SomeClass', 'oldMethod', 'AnotherExampleClass', 'newStaticMethod')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }
        foreach ($this->staticMethodRenamesByOldMethod[strtolower($methodName)] ?? [] as $staticMethodRename) {
            if (!$this->isObjectType($node->class, $staticMethodRename->getOldObjectType())) {
                continue;
            }
            return $this->rename($node, $staticMethodRename);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, RenameStaticMethod::class);
        // bucket by lowercased old method name (method names are case-insensitive)
        $this->staticMethodRenamesByOldMethod = [];
        foreach ($configuration as $staticMethodRename) {
            $this->staticMethodRenamesByOldMethod[strtolower($staticMethodRename->getOldMethod())][] = $staticMethodRename;
        }
    }
    private function rename(StaticCall $staticCall, RenameStaticMethod $renameStaticMethod): StaticCall
    {
        $staticCall->name = new Identifier($renameStaticMethod->getNewMethod());
        if ($renameStaticMethod->hasClassChanged()) {
            $staticCall->class = new FullyQualified($renameStaticMethod->getNewClass());
        }
        return $staticCall;
    }
}
