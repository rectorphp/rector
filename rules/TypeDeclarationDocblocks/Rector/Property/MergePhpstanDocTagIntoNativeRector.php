<?php

declare (strict_types=1);
namespace Rector\TypeDeclarationDocblocks\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclarationDocblocks\Rector\Property\MergePhpstanDocTagIntoNativeRector\MergePhpstanDocTagIntoNativeRectorTest
 */
final class MergePhpstanDocTagIntoNativeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    /**
     * @var array<string, string>
     */
    private const PHPSTAN_TAG_TO_NATIVE_TAG = ['@phpstan-var' => '@var', '@phpstan-param' => '@param', '@phpstan-return' => '@return'];
    public function __construct(DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Merge more precise @phpstan-var/@phpstan-param/@phpstan-return docblock tag into its native @var/@param/@return tag', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var Collection
     *
     * @phpstan-var Collection<int, string>
     */
    private $items;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var Collection<int, string>
     */
    private $items;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class, Param::class, ClassConst::class, ClassMethod::class, Function_::class];
    }
    /**
     * @param Property|Param|ClassConst|ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $phpDocNode = $phpDocInfo->getPhpDocNode();
        $phpstanParamNames = [];
        $phpstanVarNames = [];
        $hasPhpstanReturn = \false;
        foreach ($phpDocNode->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if ($phpDocChildNode->name === '@phpstan-param' && $phpDocChildNode->value instanceof ParamTagValueNode) {
                $phpstanParamNames[] = $phpDocChildNode->value->parameterName;
            } elseif ($phpDocChildNode->name === '@phpstan-var' && $phpDocChildNode->value instanceof VarTagValueNode) {
                $phpstanVarNames[] = $phpDocChildNode->value->variableName;
            } elseif ($phpDocChildNode->name === '@phpstan-return' && $phpDocChildNode->value instanceof ReturnTagValueNode) {
                $hasPhpstanReturn = \true;
            }
        }
        if ($phpstanParamNames === [] && $phpstanVarNames === [] && !$hasPhpstanReturn) {
            return null;
        }
        $hasChanged = \false;
        foreach ($phpDocNode->children as $key => $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            // drop the weaker native tag that the @phpstan-* variant overrules
            if ($this->isOverruledNativeTag($phpDocChildNode, $phpstanParamNames, $phpstanVarNames, $hasPhpstanReturn)) {
                unset($phpDocNode->children[$key]);
                $hasChanged = \true;
                continue;
            }
            // promote the @phpstan-* tag to its native counterpart
            $nativeTagName = self::PHPSTAN_TAG_TO_NATIVE_TAG[$phpDocChildNode->name] ?? null;
            if ($nativeTagName !== null) {
                $phpDocNode->children[$key] = new PhpDocTagNode($nativeTagName, $phpDocChildNode->value);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        $phpDocNode->children = array_values($phpDocNode->children);
        // drop a leading empty line left behind by a removed native tag
        while ($phpDocNode->children !== []) {
            $firstChildNode = $phpDocNode->children[0];
            if ($firstChildNode instanceof PhpDocTextNode && trim($firstChildNode->text) === '') {
                array_shift($phpDocNode->children);
                continue;
            }
            break;
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        return $node;
    }
    /**
     * @param string[] $phpstanParamNames
     * @param string[] $phpstanVarNames
     */
    private function isOverruledNativeTag(PhpDocTagNode $phpDocTagNode, array $phpstanParamNames, array $phpstanVarNames, bool $hasPhpstanReturn): bool
    {
        if ($phpDocTagNode->name === '@param' && $phpDocTagNode->value instanceof ParamTagValueNode) {
            return in_array($phpDocTagNode->value->parameterName, $phpstanParamNames, \true);
        }
        if ($phpDocTagNode->name === '@var' && $phpDocTagNode->value instanceof VarTagValueNode) {
            return in_array($phpDocTagNode->value->variableName, $phpstanVarNames, \true);
        }
        if ($phpDocTagNode->name === '@return' && $phpDocTagNode->value instanceof ReturnTagValueNode) {
            return $hasPhpstanReturn;
        }
        return \false;
    }
}
