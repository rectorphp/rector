<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\FileWithoutNamespace;

use RectorPrefix202212\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\PhpDocTypeRenamer;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202212\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\PseudoNamespaceToNamespaceRectorTest
 */
final class PseudoNamespaceToNamespaceRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @see https://regex101.com/r/chvLgs/1/
     * @var string
     */
    private const SPLIT_BY_UNDERSCORE_REGEX = '#([a-zA-Z])(_)?(_)([a-zA-Z])#';
    /**
     * @var PseudoNamespaceToNamespace[]
     */
    private $pseudoNamespacesToNamespaces = [];
    /**
     * @var string|null
     */
    private $newNamespace;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\PhpDocTypeRenamer
     */
    private $phpDocTypeRenamer;
    public function __construct(PhpDocTypeRenamer $phpDocTypeRenamer)
    {
        $this->phpDocTypeRenamer = $phpDocTypeRenamer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces defined Pseudo_Namespaces by Namespace\\Ones.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
/** @var Some_Chicken $someService */
$someService = new Some_Chicken;
$someClassToKeep = new Some_Class_To_Keep;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var Some\Chicken $someService */
$someService = new Some\Chicken;
$someClassToKeep = new Some_Class_To_Keep;
CODE_SAMPLE
, [new PseudoNamespaceToNamespace('Some_', ['Some_Class_To_Keep'])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        // property, method
        return [FileWithoutNamespace::class, Namespace_::class];
    }
    /**
     * @param Namespace_|FileWithoutNamespace $node
     */
    public function refactor(Node $node) : ?Node
    {
        $processNode = clone $node;
        $this->newNamespace = null;
        if ($processNode instanceof FileWithoutNamespace) {
            $changedStmts = $this->refactorStmts($processNode->stmts);
            if ($changedStmts === null) {
                return null;
            }
            $processNode->stmts = $changedStmts;
            // add a new namespace?
            if ($this->newNamespace !== null) {
                return new Namespace_(new Name($this->newNamespace), $changedStmts);
            }
        }
        if ($processNode instanceof Namespace_) {
            return $this->refactorNamespace($processNode);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, PseudoNamespaceToNamespace::class);
        $this->pseudoNamespacesToNamespaces = $configuration;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]|null
     */
    private function refactorStmts(array $stmts) : ?array
    {
        $hasChanged = \false;
        $this->traverseNodesWithCallable($stmts, function (Node $node) use(&$hasChanged) : ?Node {
            if (!$node instanceof Name && !$node instanceof Identifier && !$node instanceof Property && !$node instanceof FunctionLike) {
                return null;
            }
            if ($this->refactorPhpDoc($node)) {
                $hasChanged = \true;
            }
            // @todo - update rule to allow for bool instanceof check
            if ($node instanceof Name || $node instanceof Identifier) {
                $changedNode = $this->processNameOrIdentifier($node);
                if ($changedNode instanceof Node) {
                    $hasChanged = \true;
                    return $changedNode;
                }
            }
            return null;
        });
        if ($hasChanged) {
            return $stmts;
        }
        return null;
    }
    /**
     * @return Identifier|Name|null
     * @param \PhpParser\Node\Name|\PhpParser\Node\Identifier $node
     */
    private function processNameOrIdentifier($node) : ?Node
    {
        // no name → skip
        if ($node->toString() === '') {
            return null;
        }
        foreach ($this->pseudoNamespacesToNamespaces as $pseudoNamespaceToNamespace) {
            if (!$this->isName($node, $pseudoNamespaceToNamespace->getNamespacePrefix() . '*')) {
                continue;
            }
            $excludedClasses = $pseudoNamespaceToNamespace->getExcludedClasses();
            if ($excludedClasses !== [] && $this->isNames($node, $excludedClasses)) {
                return null;
            }
            if ($node instanceof Name) {
                return $this->processName($node);
            }
            return $this->processIdentifier($node);
        }
        return null;
    }
    private function processName(Name $name) : Name
    {
        $nodeName = $this->getName($name);
        $name->parts = \explode('_', $nodeName);
        return $name;
    }
    private function processIdentifier(Identifier $identifier) : ?Identifier
    {
        $parentNode = $identifier->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof ClassLike) {
            return null;
        }
        $name = $this->getName($identifier);
        if ($name === null) {
            return null;
        }
        /** @var string $namespaceName */
        $namespaceName = Strings::before($name, '_', -1);
        /** @var string $lastNewNamePart */
        $lastNewNamePart = Strings::after($name, '_', -1);
        $newNamespace = Strings::replace($namespaceName, self::SPLIT_BY_UNDERSCORE_REGEX, '$1$2\\\\$4');
        if ($this->newNamespace !== null && $this->newNamespace !== $newNamespace) {
            throw new ShouldNotHappenException('There cannot be 2 different namespaces in one file');
        }
        $this->newNamespace = $newNamespace;
        $identifier->name = $lastNewNamePart;
        return $identifier;
    }
    private function refactorNamespace(Namespace_ $namespace) : ?Namespace_
    {
        $changedStmts = $this->refactorStmts($namespace->stmts);
        if ($changedStmts === null) {
            return null;
        }
        return $namespace;
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\FunctionLike|\PhpParser\Node\Identifier|\PhpParser\Node\Stmt\Property $node
     */
    private function refactorPhpDoc($node) : bool
    {
        $hasChanged = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        // replace on @var/@param/@return/@throws
        foreach ($this->pseudoNamespacesToNamespaces as $pseudoNamespaceToNamespace) {
            $hasDocTypeChanged = $this->phpDocTypeRenamer->changeUnderscoreType($phpDocInfo, $node, $pseudoNamespaceToNamespace);
            if ($hasDocTypeChanged) {
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
}
