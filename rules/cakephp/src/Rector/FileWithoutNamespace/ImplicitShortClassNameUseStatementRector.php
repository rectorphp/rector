<?php

declare(strict_types=1);

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
 * @see \Rector\CakePHP\Tests\Rector\FileWithoutNamespace\ImplicitShortClassNameUseStatementRector\ImplicitShortClassNameUseStatementRectorTest
 */
final class ImplicitShortClassNameUseStatementRector extends AbstractRector
{
    /**
     * @var ImplicitNameResolver
     */
    private $implicitNameResolver;

    public function __construct(ImplicitNameResolver $implicitNameResolver)
    {
        $this->implicitNameResolver = $implicitNameResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Collect implicit class names and add imports', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use App\Foo\Plugin;

class LocationsFixture extends TestFixture implements Plugin
{
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
use App\Foo\Plugin;
use Cake\TestSuite\Fixture\TestFixture;

class LocationsFixture extends TestFixture implements Plugin
{
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FileWithoutNamespace::class];
    }

    /**
     * @param FileWithoutNamespace $node
     */
    public function refactor(Node $node): ?Node
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
        $node->stmts = array_merge($uses, $node->stmts);

        return $node;
    }

    /**
     * @return Name[]
     */
    private function findNames(FileWithoutNamespace $fileWithoutNamespace): array
    {
        return $this->betterNodeFinder->find($fileWithoutNamespace->stmts, function (Node $node): bool {
            if (! $node instanceof Name) {
                return false;
            }

            $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
            return ! $parent instanceof New_;
        });
    }

    /**
     * @param Name[] $names
     * @return string[]
     */
    private function resolveNames(array $names): array
    {
        $resolvedNames = [];
        foreach ($names as $name) {
            $classShortName = $this->getName($name);
            $resolvedName = $this->implicitNameResolver->resolve($classShortName);
            if ($resolvedName === null) {
                continue;
            }

            $resolvedNames[] = $resolvedName;
        }

        return $resolvedNames;
    }
}
