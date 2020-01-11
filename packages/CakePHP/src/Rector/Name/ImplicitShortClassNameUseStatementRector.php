<?php

declare(strict_types=1);

namespace Rector\CakePHP\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use Rector\CakePHP\ImplicitNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/cakephp/upgrade/blob/05d85c147bb1302b576b818cabb66a40462aaed0/src/Shell/Task/AppUsesTask.php#L183
 *
 * @see \Rector\CakePHP\Tests\Rector\Name\ImplicitShortClassNameUseStatementRector\ImplicitShortClassNameUseStatementRectorTest
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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Collect implicit class names and add imports', [
            new CodeSample(
                <<<'PHP'
use App\Foo\Plugin;

class LocationsFixture extends TestFixture implements Plugin
{
}
PHP
,
                <<<'PHP'
use App\Foo\Plugin;
use Cake\TestSuite\Fixture\TestFixture;

class LocationsFixture extends TestFixture implements Plugin
{
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Name::class];
    }

    /**
     * @param Name $node
     */
    public function refactor(Node $node): ?Node
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof ClassLike && $parentNode instanceof New_) {
            return null;
        }

        $classShortName = $this->getName($node);
        $resvoledName = $this->implicitNameResolver->resolve($classShortName);
        if ($resvoledName === null) {
            return null;
        }

        $this->addUseType(new FullyQualifiedObjectType($resvoledName), $node);

        return null;
    }
}
