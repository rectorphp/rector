<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer;
use Rector\Renaming\Contract\RenameAnnotationInterface;
use Rector\Renaming\ValueObject\RenameAnnotationByType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202308\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\ClassMethod\RenameAnnotationRector\RenameAnnotationRectorTest
 */
final class RenameAnnotationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockTagReplacer
     */
    private $docBlockTagReplacer;
    /**
     * @var RenameAnnotationInterface[]
     */
    private $renameAnnotations = [];
    public function __construct(DocBlockTagReplacer $docBlockTagReplacer)
    {
        $this->docBlockTagReplacer = $docBlockTagReplacer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns defined annotations above properties and methods to their new values.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @test
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    /**
     * @scenario
     */
    public function someMethod()
    {
    }
}
CODE_SAMPLE
, [new RenameAnnotationByType('PHPUnit\\Framework\\TestCase', 'test', 'scenario')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, Expression::class];
    }
    /**
     * @param Class_|Expression $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        if ($node instanceof Expression) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
            foreach ($this->renameAnnotations as $renameAnnotation) {
                $hasDocBlockChanged = $this->docBlockTagReplacer->replaceTagByAnother($phpDocInfo, $renameAnnotation->getOldAnnotation(), $renameAnnotation->getNewAnnotation());
                if ($hasDocBlockChanged) {
                    $hasChanged = \true;
                }
            }
            if ($hasChanged) {
                return $node;
            }
            return null;
        }
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof ClassMethod && !$stmt instanceof Property) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($stmt);
            foreach ($this->renameAnnotations as $renameAnnotation) {
                if ($renameAnnotation instanceof RenameAnnotationByType && !$this->isObjectType($node, $renameAnnotation->getObjectType())) {
                    continue;
                }
                $hasDocBlockChanged = $this->docBlockTagReplacer->replaceTagByAnother($phpDocInfo, $renameAnnotation->getOldAnnotation(), $renameAnnotation->getNewAnnotation());
                if ($hasDocBlockChanged) {
                    $hasChanged = \true;
                }
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, RenameAnnotationInterface::class);
        $this->renameAnnotations = $configuration;
    }
}
