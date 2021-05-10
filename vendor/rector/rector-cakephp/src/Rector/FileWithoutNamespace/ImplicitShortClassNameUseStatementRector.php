<?php

declare (strict_types=1);
namespace Rector\CakePHP\Rector\FileWithoutNamespace;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use Rector\CakePHP\ImplicitNameResolver;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/cakephp/upgrade/blob/05d85c147bb1302b576b818cabb66a40462aaed0/src/Shell/Task/AppUsesTask.php#L183
 *
 * @see \aRector\CakePHP\Tests\Rector\FileWithoutNamespace\ImplicitShortClassNameUseStatementRector\ImplicitShortClassNameUseStatementRectorTest
 */
final class ImplicitShortClassNameUseStatementRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ImplicitNameResolver
     */
    private $implicitNameResolver;
    public function __construct(\Rector\CakePHP\ImplicitNameResolver $implicitNameResolver)
    {
        $this->implicitNameResolver = $implicitNameResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Collect implicit class names and add imports', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use App\Foo\Plugin;

class LocationsFixture extends TestFixture implements Plugin
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use App\Foo\Plugin;
use Cake\TestSuite\Fixture\TestFixture;

class LocationsFixture extends TestFixture implements Plugin
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class];
    }
    /**
     * @param FileWithoutNamespace $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $names = $this->findNames($node);
        if ($names === []) {
            return null;
        }
        $resolvedNames = $this->resolveNames($names);
        if ($resolvedNames === []) {
            return null;
        }
        $uses = $this->nodeFactory->createUsesFromNames($resolvedNames);
        $node->stmts = \array_merge($uses, $node->stmts);
        return $node;
    }
    /**
     * @return Name[]
     */
    private function findNames(\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $fileWithoutNamespace) : array
    {
        return $this->betterNodeFinder->find($fileWithoutNamespace->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Name) {
                return \false;
            }
            $parent = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            return !$parent instanceof \PhpParser\Node\Expr\New_;
        });
    }
    /**
     * @param Name[] $names
     * @return string[]
     */
    private function resolveNames(array $names) : array
    {
        $resolvedNames = [];
        foreach ($names as $name) {
            $classShortName = $this->getName($name);
            if ($classShortName === null) {
                continue;
            }
            $resolvedName = $this->implicitNameResolver->resolve($classShortName);
            if ($resolvedName === null) {
                continue;
            }
            $resolvedNames[] = $resolvedName;
        }
        return $resolvedNames;
    }
}
