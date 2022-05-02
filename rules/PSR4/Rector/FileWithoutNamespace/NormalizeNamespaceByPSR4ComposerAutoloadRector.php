<?php

declare (strict_types=1);
namespace Rector\PSR4\Rector\FileWithoutNamespace;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\NodeAnalyzer\InlineHTMLAnalyzer;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Rector\PSR4\NodeManipulator\FullyQualifyStmtsAnalyzer;
use Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ComposerJsonAwareCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\PSR4\Rector\FileWithoutNamespace\NormalizeNamespaceByPSR4ComposerAutoloadRector\NormalizeNamespaceByPSR4ComposerAutoloadRectorTest
 */
final class NormalizeNamespaceByPSR4ComposerAutoloadRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface
     */
    private $psr4AutoloadNamespaceMatcher;
    /**
     * @readonly
     * @var \Rector\PSR4\NodeManipulator\FullyQualifyStmtsAnalyzer
     */
    private $fullyQualifyStmtsAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\InlineHTMLAnalyzer
     */
    private $inlineHTMLAnalyzer;
    public function __construct(\Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface $psr4AutoloadNamespaceMatcher, \Rector\PSR4\NodeManipulator\FullyQualifyStmtsAnalyzer $fullyQualifyStmtsAnalyzer, \Rector\Core\NodeAnalyzer\InlineHTMLAnalyzer $inlineHTMLAnalyzer)
    {
        $this->psr4AutoloadNamespaceMatcher = $psr4AutoloadNamespaceMatcher;
        $this->fullyQualifyStmtsAnalyzer = $fullyQualifyStmtsAnalyzer;
        $this->inlineHTMLAnalyzer = $inlineHTMLAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        $description = \sprintf('Adds namespace to namespace-less files or correct namespace to match PSR-4 in `composer.json` autoload section. Run with combination with "%s"', \Rector\PSR4\Rector\Namespace_\MultipleClassFileToPsr4ClassesRector::class);
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition($description, [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ComposerJsonAwareCodeSample(<<<'CODE_SAMPLE'
// src/SomeClass.php

class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
// src/SomeClass.php

namespace App\CustomNamespace;

class SomeClass
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
{
    "autoload": {
        "psr-4": {
            "App\\CustomNamespace\\": "src"
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Namespace_::class, \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class];
    }
    /**
     * @param FileWithoutNamespace|Namespace_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $processNode = clone $node;
        if ($this->inlineHTMLAnalyzer->hasInlineHTML($processNode)) {
            return null;
        }
        $expectedNamespace = $this->psr4AutoloadNamespaceMatcher->getExpectedNamespace($this->file, $processNode);
        if ($expectedNamespace === null) {
            return null;
        }
        // is namespace and already correctly named?
        if ($processNode instanceof \PhpParser\Node\Stmt\Namespace_ && $this->nodeNameResolver->isCaseSensitiveName($processNode, $expectedNamespace)) {
            return null;
        }
        if ($processNode instanceof \PhpParser\Node\Stmt\Namespace_ && $this->hasNamespaceInPreviousNamespace($processNode)) {
            return null;
        }
        // to put declare_strict types on correct place
        if ($processNode instanceof \Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace) {
            return $this->refactorFileWithoutNamespace($processNode, $expectedNamespace);
        }
        $processNode->name = new \PhpParser\Node\Name($expectedNamespace);
        $this->fullyQualifyStmtsAnalyzer->process($processNode->stmts);
        return $processNode;
    }
    private function hasNamespaceInPreviousNamespace(\PhpParser\Node\Stmt\Namespace_ $namespace) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($namespace, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Stmt\Namespace_;
        });
    }
    private function refactorFileWithoutNamespace(\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace $fileWithoutNamespace, string $expectedNamespace) : \PhpParser\Node\Stmt\Namespace_
    {
        $nodes = $fileWithoutNamespace->stmts;
        $nodesWithStrictTypesThenNamespace = [];
        foreach ($nodes as $key => $fileWithoutNamespace) {
            if ($fileWithoutNamespace instanceof \PhpParser\Node\Stmt\Declare_) {
                $nodesWithStrictTypesThenNamespace[] = $fileWithoutNamespace;
                unset($nodes[$key]);
            }
        }
        $namespace = new \PhpParser\Node\Stmt\Namespace_(new \PhpParser\Node\Name($expectedNamespace), $nodes);
        $nodesWithStrictTypesThenNamespace[] = $namespace;
        $this->fullyQualifyStmtsAnalyzer->process($nodes);
        return $namespace;
    }
}
