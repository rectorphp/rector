<?php

declare(strict_types=1);

namespace Rector\PSR4\Rector\Namespace_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ComposerJsonAwareCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\PSR4\Tests\Rector\Namespace_\NormalizeNamespaceByPSR4ComposerAutoloadRector\NormalizeNamespaceByPSR4ComposerAutoloadRectorTest
 */
final class NormalizeNamespaceByPSR4ComposerAutoloadRector extends AbstractRector
{
    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @var PSR4AutoloadNamespaceMatcherInterface
     */
    private $psr4AutoloadNamespaceMatcher;

    public function __construct(
        PSR4AutoloadNamespaceMatcherInterface $psr4AutoloadNamespaceMatcher,
        RenamedClassesCollector $renamedClassesCollector
    ) {
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->psr4AutoloadNamespaceMatcher = $psr4AutoloadNamespaceMatcher;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes namespace and class names to match PSR-4 in composer.json autoload section', [
                new ComposerJsonAwareCodeSample(
                <<<'CODE_SAMPLE'
// src/SomeClass.php

namespace App\DifferentNamespace;

class SomeClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
// src/SomeClass.php

namespace App\CustomNamespace;

class SomeClass
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
{
    "autoload": {
        "psr-4": {
            "App\\CustomNamespace\\": "src"
        }
    }
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
        return [Namespace_::class];
    }

    /**
     * @param Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $expectedNamespace = $this->psr4AutoloadNamespaceMatcher->getExpectedNamespace($node);
        if ($expectedNamespace === null) {
            return null;
        }

        $currentNamespace = $this->getName($node);

        // namespace is correct → skip
        if ($currentNamespace === $expectedNamespace) {
            return null;
        }

        // change it
        $node->name = new Name($expectedNamespace);

        // add use import for classes from the same namespace
        $this->traverseNodesWithCallable($node, function (Node $node) use ($currentNamespace): ?void {
            if (! $node instanceof Name) {
                return null;
            }

            /** @var Name|null $originalName */
            $originalName = $node->getAttribute(AttributeKey::ORIGINAL_NAME);
            if (! $originalName instanceof Name) {
                return null;
            }

            $sameNamespacedName = $currentNamespace . '\\' . $originalName->toString();

            if (! $this->isName($node, $sameNamespacedName)) {
                return null;
            }

            // this needs to be imported
            $objectType = $this->getName($node);
            $this->addUseType(new FullyQualifiedObjectType($objectType), $node);
        });

        /** @var SmartFileInfo $smartFileInfo */
        $smartFileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        $oldClassName = $currentNamespace . '\\' . $smartFileInfo->getBasenameWithoutSuffix();
        $newClassName = $expectedNamespace . '\\' . $smartFileInfo->getBasenameWithoutSuffix();

        $this->renamedClassesCollector->addClassRename($oldClassName, $newClassName);

        // collect changed class
        return $node;
    }
}
