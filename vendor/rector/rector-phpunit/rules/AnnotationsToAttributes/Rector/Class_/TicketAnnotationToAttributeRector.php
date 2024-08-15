<?php

declare (strict_types=1);
namespace Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.phpunit.de/en/10.0/annotations.html#ticket
 *
 * @see \Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\Class_\TicketAnnotationToAttributeRector\TicketAnnotationToAttributeRectorTest
 */
final class TicketAnnotationToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @var string
     */
    private const TICKET_CLASS = 'PHPUnit\\Framework\\Attributes\\Ticket';
    public function __construct(PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change annotations with value to attribute', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

/**
 * @ticket 123
 */
final class SomeTest extends TestCase
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Ticket;

#[Ticket('123')]
final class SomeTest extends TestCase
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, ClassMethod::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        /** @var PhpDocTagNode[] $ticketTagValueNodes */
        $ticketTagValueNodes = $phpDocInfo->getTagsByName('ticket');
        if ($ticketTagValueNodes === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($ticketTagValueNodes as $ticketTagValueNode) {
            if (!$ticketTagValueNode->value instanceof GenericTagValueNode) {
                continue;
            }
            $stringValue = $ticketTagValueNode->value->value;
            $attribute = $this->createTicketAttribute($stringValue);
            $node->attrGroups[] = new AttributeGroup([$attribute]);
            // cleanup
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $ticketTagValueNode);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
            return $node;
        }
        return null;
    }
    private function createTicketAttribute(string $stringValue) : Attribute
    {
        $fullyQualified = new FullyQualified(self::TICKET_CLASS);
        $ticketString = new String_($stringValue);
        $args = [new Arg($ticketString)];
        return new Attribute($fullyQualified, $args);
    }
}
