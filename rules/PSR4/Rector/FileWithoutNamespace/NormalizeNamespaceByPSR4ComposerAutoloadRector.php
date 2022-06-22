<?php

declare (strict_types=1);
namespace Rector\PSR4\Rector\FileWithoutNamespace;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
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
final class NormalizeNamespaceByPSR4ComposerAutoloadRector extends AbstractRector
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
    public function __construct(PSR4AutoloadNamespaceMatcherInterface $psr4AutoloadNamespaceMatcher, FullyQualifyStmtsAnalyzer $fullyQualifyStmtsAnalyzer, InlineHTMLAnalyzer $inlineHTMLAnalyzer)
    {
        $this->psr4AutoloadNamespaceMatcher = $psr4AutoloadNamespaceMatcher;
        $this->fullyQualifyStmtsAnalyzer = $fullyQualifyStmtsAnalyzer;
        $this->inlineHTMLAnalyzer = $inlineHTMLAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        $description = \sprintf('Adds namespace to namespace-less files or correct namespace to match PSR-4 in `composer.json` autoload section. Run with combination with "%s"', MultipleClassFileToPsr4ClassesRector::class);
        return new RuleDefinition($description, [new ComposerJsonAwareCodeSample(<<<'CODE_SAMPLE'
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
        return [Namespace_::class, FileWithoutNamespace::class];
    }
    /**
     * @param FileWithoutNamespace|Namespace_ $node
     * @return Node|null|Stmt[]
     */
    public function refactor(Node $node)
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
        if ($processNode instanceof Namespace_ && $this->nodeNameResolver->isCaseSensitiveName($processNode, $expectedNamespace)) {
            return null;
        }
        if ($processNode instanceof Namespace_ && $this->hasNamespaceInPreviousNamespace($processNode)) {
            return null;
        }
        // to put declare_strict types on correct place
        if ($processNode instanceof FileWithoutNamespace) {
            return $this->refactorFileWithoutNamespace($processNode, $expectedNamespace);
        }
        $processNode->name = new Name($expectedNamespace);
        $this->fullyQualifyStmtsAnalyzer->process($processNode->stmts);
        return $processNode;
    }
    private function hasNamespaceInPreviousNamespace(Namespace_ $namespace) : bool
    {
        return (bool) $this->betterNodeFinder->findFirstPrevious($namespace, static function (Node $node) : bool {
            return $node instanceof Namespace_;
        });
    }
    /**
     * @return Namespace_|Stmt[]
     */
    private function refactorFileWithoutNamespace(FileWithoutNamespace $fileWithoutNamespace, string $expectedNamespace)
    {
        $nodes = $fileWithoutNamespace->stmts;
        $declare = null;
        $nodesWithStrictTypesThenNamespace = [];
        foreach ($nodes as $key => $fileWithoutNamespace) {
            if ($key > 0) {
                break;
            }
            if ($fileWithoutNamespace instanceof Declare_) {
                $declare = $fileWithoutNamespace;
                unset($nodes[$key]);
            }
        }
        $namespace = new Namespace_(new Name($expectedNamespace), $nodes);
        $nodesWithStrictTypesThenNamespace[] = $namespace;
        $this->fullyQualifyStmtsAnalyzer->process($nodes);
        if ($declare instanceof Declare_) {
            return [$declare, $namespace];
        }
        return $namespace;
    }
}
