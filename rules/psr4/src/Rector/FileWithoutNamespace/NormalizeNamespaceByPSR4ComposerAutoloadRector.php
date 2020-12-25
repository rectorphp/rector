<?php

declare(strict_types=1);

namespace Rector\PSR4\Rector\FileWithoutNamespace;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Configuration\Option;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ComposerJsonAwareCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\PSR4\Tests\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\NormalizeNamespaceByPSR4ComposerAutoloadRectorTest
 */
final class NormalizeNamespaceByPSR4ComposerAutoloadRector extends AbstractRector
{
    /**
     * @var PSR4AutoloadNamespaceMatcherInterface
     */
    private $psr4AutoloadNamespaceMatcher;

    public function __construct(PSR4AutoloadNamespaceMatcherInterface $psr4AutoloadNamespaceMatcher)
    {
        $this->psr4AutoloadNamespaceMatcher = $psr4AutoloadNamespaceMatcher;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        $description = sprintf(
            'Adds namespace to namespace-less files or correct namespace to match PSR-4 in `composer.json` autoload section. Run with combination with "%s"',
            MultipleClassFileToPsr4ClassesRector::class
        );

        return new RuleDefinition($description, [
            new ComposerJsonAwareCodeSample(
                <<<'CODE_SAMPLE'
// src/SomeClass.php

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
        return [Namespace_::class, FileWithoutNamespace::class];
    }

    /**
     * @param FileWithoutNamespace|Namespace_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $expectedNamespace = $this->psr4AutoloadNamespaceMatcher->getExpectedNamespace($node);
        if ($expectedNamespace === null) {
            return null;
        }

        // is namespace and already correctly named?
        if ($node instanceof Namespace_ && $this->isName($node, $expectedNamespace)) {
            return null;
        }

        // to put declare_strict types on correct place
        if ($node instanceof FileWithoutNamespace) {
            return $this->refactorFileWithoutNamespace($node, $expectedNamespace);
        }

        if ($node instanceof Namespace_) {
            $node->name = new Name($expectedNamespace);
            $this->makeNamesFullyQualified($node->stmts);
        }

        return $node;
    }

    private function refactorFileWithoutNamespace(
        FileWithoutNamespace $fileWithoutNamespace,
        string $expectedNamespace
    ): FileWithoutNamespace {
        $nodes = $fileWithoutNamespace->stmts;

        $nodesWithStrictTypesThenNamespace = [];
        foreach ($nodes as $key => $fileWithoutNamespace) {
            if ($fileWithoutNamespace instanceof Declare_) {
                $nodesWithStrictTypesThenNamespace[] = $fileWithoutNamespace;
                unset($nodes[$key]);
            }
        }

        $namespace = new Namespace_(new Name($expectedNamespace), $nodes);
        $nodesWithStrictTypesThenNamespace[] = $namespace;

        $this->makeNamesFullyQualified($nodes);

        // @todo update to a new class node, like FileWithNamespace
        return new FileWithoutNamespace($nodesWithStrictTypesThenNamespace);
    }

    /**
     * @param Stmt[] $nodes
     */
    private function makeNamesFullyQualified(array $nodes): void
    {
        // no need to
        if ($this->parameterProvider->provideBoolParameter(Option::AUTO_IMPORT_NAMES)) {
            return;
        }

        // FQNize all class names
        $this->traverseNodesWithCallable($nodes, function (Node $node): ?FullyQualified {
            if (! $node instanceof Name) {
                return null;
            }

            return new FullyQualified($this->getName($node));
        });
    }
}
