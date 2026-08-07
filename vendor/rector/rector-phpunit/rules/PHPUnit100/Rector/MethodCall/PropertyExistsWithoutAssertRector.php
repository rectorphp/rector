<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit100\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use Rector\PHPUnit\NodeAnalyzer\IdentifierManipulator;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @url https://github.com/sebastianbergmann/phpunit/issues/4601
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit100\Rector\MethodCall\PropertyExistsWithoutAssertRector\PropertyExistsWithoutAssertRectorTest
 */
final class PropertyExistsWithoutAssertRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private IdentifierManipulator $identifierManipulator;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * inherited from the PHPUnit 10.0 set
     */
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('phpunit/phpunit', '>=10.0');
    }
    /**
     * The assertObjectHasAttribute() and assertObjectNotHasAttribute() methods are left out on purpose,
     * as they have a direct replacement in assertObjectHasProperty() and assertObjectNotHasProperty().
     * Those renames are handled by RenameMethodRector in the composer-based set.
     *
     * @var array<string, string>
     */
    private const RENAME_METHODS_WITH_OBJECT_MAP = [
        'assertClassHasAttribute' => 'assertTrue',
        'assertClassHasStaticAttribute' => 'assertTrue',
        // false
        'assertClassNotHasStaticAttribute' => 'assertFalse',
        'assertClassNotHasAttribute' => 'assertFalse',
        // no assert
        'objectHasAttribute' => 'assertTrue',
        'classHasAttribute' => 'assertTrue',
        'classHasStaticAttribute' => 'assertTrue',
    ];
    public function __construct(IdentifierManipulator $identifierManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->identifierManipulator = $identifierManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace removed assertClassHas*Attribute() with property_exists()', [new CodeSample(<<<'CODE_SAMPLE'
$this->assertClassHasAttribute("Class", "property");
$this->assertClassHasStaticAttribute("Class", "property");
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->assertTrue(property_exists("Class", "property"));
$this->assertTrue(property_exists("Class", "property"));
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $map = self::RENAME_METHODS_WITH_OBJECT_MAP;
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, array_keys($map))) {
            return null;
        }
        if ($node->isFirstClassCallable() || !isset($node->getArgs()[0], $node->getArgs()[1])) {
            return null;
        }
        $firstNode = new Arg($node->getArgs()[0]->value);
        if ($node->getArgs()[1]->value instanceof ClassConstFetch) {
            $secondNode = $node->getArgs()[1];
        } else {
            $secondNode = new Arg($node->getArgs()[1]->value);
        }
        $funcCall = new FuncCall(new Name('property_exists'), [$secondNode, $firstNode]);
        $newArgs = $this->nodeFactory->createArgs([$funcCall]);
        unset($node->args[0], $node->args[1]);
        $node->args = array_merge($newArgs, $node->getArgs());
        $this->identifierManipulator->renameNodeWithMap($node, $map);
        return $node;
    }
}
