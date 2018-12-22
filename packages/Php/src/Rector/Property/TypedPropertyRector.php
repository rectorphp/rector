<?php declare(strict_types=1);

namespace Rector\Php\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Php\VarTypeInfo;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Php\PhpTypeSupport;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @source https://wiki.php.net/rfc/typed_properties_v2#proposal
 */
final class TypedPropertyRector extends AbstractRector
{
    /**
     * @var string
     */
    public const PHP74_PROPERTY_TYPE = 'php74_property_type';

    /**
     * @var string[][]
     */
    private $typeNameToAllowedDefaultNodeType = [
        'string' => [String_::class],
        'bool' => [ConstFetch::class],
        'array' => [Array_::class],
        'float' => [DNumber::class, LNumber::class],
        'int' => [LNumber::class],
        'iterable' => [Array_::class],
    ];

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    public function __construct(DocBlockAnalyzer $docBlockAnalyzer)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;

        // PHP 7.4 already knows "object"
        PhpTypeSupport::enableType('object');
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes property `@var` annotations from annotation to type.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass 
{
    /** 
     * @var int 
     */
    private count; 
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
final class SomeClass 
{
    private int count; 
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        // non FQN, so they are 1:1 to possible imported doc type
        $varTypeInfo = $this->docBlockAnalyzer->getVarTypeInfo($node);
        if ($varTypeInfo === null) {
            return null;
        }

        if ($varTypeInfo->isTypehintAble() === false) {
            return null;
        }

        if (! $this->matchesDocTypeAndDefaultValueType($varTypeInfo, $node)) {
            return null;
        }

        $node->setAttribute(self::PHP74_PROPERTY_TYPE, $varTypeInfo->getTypeNode());

        // invoke the print, because only attribute has changed
        $node->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $node;
    }

    private function matchesDocTypeAndDefaultValueType(VarTypeInfo $varTypeInfo, Property $propertyNode): bool
    {
        $defaultValueNode = $propertyNode->props[0]->default;
        if ($defaultValueNode === null) {
            return true;
        }

        if (! isset($this->typeNameToAllowedDefaultNodeType[$varTypeInfo->getType()])) {
            return true;
        }

        if ($varTypeInfo->isNullable()) {
            // is default value "null"?
            return $this->isNull($defaultValueNode);
        }

        $allowedDefaultNodeTypes = $this->typeNameToAllowedDefaultNodeType[$varTypeInfo->getType()];

        return $this->matchesDefaultValueToExpectedNodeTypes($varTypeInfo, $allowedDefaultNodeTypes, $defaultValueNode);
    }

    /**
     * @param string[] $allowedDefaultNodeTypes
     */
    private function matchesDefaultValueToExpectedNodeTypes(
        VarTypeInfo $varTypeInfo,
        array $allowedDefaultNodeTypes,
        Expr $defaultValueNode
    ): bool {
        foreach ($allowedDefaultNodeTypes as $allowedDefaultNodeType) {
            if (is_a($defaultValueNode, $allowedDefaultNodeType, true)) {
                if ($varTypeInfo->getType() === 'bool') {
                    return $this->isBool($defaultValueNode);
                }

                return true;
            }
        }

        return false;
    }
}
