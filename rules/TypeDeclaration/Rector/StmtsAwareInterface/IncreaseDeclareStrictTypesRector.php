<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\StmtsAwareInterface;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;
use PhpParser\Node\Stmt\Nop;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202411\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\StmtsAwareInterface\IncreaseDeclareStrictTypesRector\IncreaseDeclareStrictTypesRectorTest
 */
final class IncreaseDeclareStrictTypesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\DeclareStrictTypeFinder
     */
    private $declareStrictTypeFinder;
    private const LIMIT = 'limit';
    /**
     * @var int
     */
    private $limit = 10;
    /**
     * @var int
     */
    private $changedItemCount = 0;
    public function __construct(DeclareStrictTypeFinder $declareStrictTypeFinder)
    {
        $this->declareStrictTypeFinder = $declareStrictTypeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add declare strict types to a limited amount of classes at a time, to try out in the wild and increase level gradually', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
function someFunction()
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
declare(strict_types=1);

function someFunction()
{
}
CODE_SAMPLE
, [self::LIMIT => 10])]);
    }
    /**
     * @param Stmt[] $nodes
     * @return Stmt[]|null
     */
    public function beforeTraverse(array $nodes) : ?array
    {
        parent::beforeTraverse($nodes);
        if ($nodes === []) {
            return null;
        }
        $rootStmt = \current($nodes);
        $stmt = $rootStmt;
        // skip classes without namespace for safety reasons
        if ($rootStmt instanceof FileWithoutNamespace) {
            return null;
        }
        if ($this->declareStrictTypeFinder->hasDeclareStrictTypes($stmt)) {
            return null;
        }
        // keep change withing a limit
        if ($this->changedItemCount >= $this->limit) {
            return null;
        }
        ++$this->changedItemCount;
        $strictTypesDeclare = $this->creteStrictTypesDeclare();
        $rectorWithLineChange = new RectorWithLineChange(self::class, $stmt->getLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
        return \array_merge([$strictTypesDeclare, new Nop()], $nodes);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        // workaround, as Rector now only hooks to specific nodes, not arrays
        return null;
    }
    public function configure(array $configuration) : void
    {
        Assert::keyExists($configuration, self::LIMIT);
        $this->limit = (int) $configuration[self::LIMIT];
    }
    private function creteStrictTypesDeclare() : Declare_
    {
        $declareDeclare = new DeclareDeclare(new Identifier('strict_types'), new LNumber(1));
        return new Declare_([$declareDeclare]);
    }
}
