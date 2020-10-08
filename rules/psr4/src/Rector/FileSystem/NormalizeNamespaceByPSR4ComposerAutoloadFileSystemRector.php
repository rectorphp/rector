<?php

declare(strict_types=1);

namespace Rector\PSR4\Rector\FileSystem;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ComposerJsonAwareCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Rector\PSR4\Rector\MultipleClassFileToPsr4ClassesRector;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\PSR4\Tests\Rector\FileSystem\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector\NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRectorTest
 */
final class NormalizeNamespaceByPSR4ComposerAutoloadFileSystemRector extends AbstractRector
{
    /**
     * @var PSR4AutoloadNamespaceMatcherInterface
     */
    private $psr4AutoloadNamespaceMatcher;

    public function __construct(PSR4AutoloadNamespaceMatcherInterface $psr4AutoloadNamespaceMatcher)
    {
        $this->psr4AutoloadNamespaceMatcher = $psr4AutoloadNamespaceMatcher;
    }

    public function getDefinition(): RectorDefinition
    {
        $description = sprintf(
            'Adds namespace to namespace-less files or correct namespace to match PSR-4 in `composer.json` autoload section. Run with combination with %s',
            MultipleClassFileToPsr4ClassesRector::class
        );

        return new RectorDefinition($description, [
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
            $nodes = $node->stmts;

            $nodesWithStrictTypesThenNamespace = [];
            foreach ($nodes as $key => $node) {
                if ($node instanceof Declare_) {
                    $nodesWithStrictTypesThenNamespace[] = $node;
                    unset($nodes[$key]);
                }
            }

            $namespace = new Namespace_(new Name($expectedNamespace), $nodes);
            $nodesWithStrictTypesThenNamespace[] = $namespace;

            // @todo update to a new class node, like FileWithNamespace
            return new FileWithoutNamespace($nodesWithStrictTypesThenNamespace);
        }

        if ($node instanceof Namespace_) {
            $node->name = new Name($expectedNamespace);
        }

        return $node;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class, FileWithoutNamespace::class];
    }
}
