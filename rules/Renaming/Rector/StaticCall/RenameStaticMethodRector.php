<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\ValueObject\RenameStaticMethod;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\StaticCall\RenameStaticMethodRector\RenameStaticMethodRectorTest
 */
final class RenameStaticMethodRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var RenameStaticMethod[]
     */
    private $staticMethodRenames = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns method names to new ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample('SomeClass::oldStaticMethod();', 'AnotherExampleClass::newStaticMethod();', [new \Rector\Renaming\ValueObject\RenameStaticMethod('SomeClass', 'oldMethod', 'AnotherExampleClass', 'newStaticMethod')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->staticMethodRenames as $staticMethodRename) {
            if (!$this->isObjectType($node->class, $staticMethodRename->getOldObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $staticMethodRename->getOldMethod())) {
                continue;
            }
            return $this->rename($node, $staticMethodRename);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Renaming\ValueObject\RenameStaticMethod::class);
        $this->staticMethodRenames = $configuration;
    }
    private function rename(\PhpParser\Node\Expr\StaticCall $staticCall, \Rector\Renaming\ValueObject\RenameStaticMethod $renameStaticMethod) : \PhpParser\Node\Expr\StaticCall
    {
        $staticCall->name = new \PhpParser\Node\Identifier($renameStaticMethod->getNewMethod());
        if ($renameStaticMethod->hasClassChanged()) {
            $staticCall->class = new \PhpParser\Node\Name\FullyQualified($renameStaticMethod->getNewClass());
        }
        return $staticCall;
    }
}
