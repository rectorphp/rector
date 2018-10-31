<?php declare(strict_types=1);

namespace Rector\Php\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Php\TypeAnalyzer;
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
     * @var bool
     */
    private $isNullableType = false;

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

    /**
     * @var TypeAnalyzer
     */
    private $typeAnalyzer;

    public function __construct(DocBlockAnalyzer $docBlockAnalyzer, TypeAnalyzer $typeAnalyzer)
    {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->typeAnalyzer = $typeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes property @var annotations from annotation to type.',
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
        $varTypes = $this->docBlockAnalyzer->getNonFqnVarTypes($node);

        // too many types to handle
        if (count($varTypes) > 2) {
            return null;
        }

        $this->isNullableType = in_array('null', $varTypes, true);

        // exactly 1 type only can be changed || 2 types with nullable; nothing else
        if (count($varTypes) !== 1 && (count($varTypes) === 2 && ! $this->isNullableType)) {
            return null;
        }

        $propertyType = $this->getPropertyTypeWithoutNull($varTypes);
        $propertyType = $this->shortenLongType($propertyType);
        if (! $this->typeAnalyzer->isPropertyTypeHintableType($propertyType)) {
            return null;
        }

        if (! $this->matchesDocTypeAndDefaultValueType($propertyType, $node)) {
            return null;
        }

        if ($this->isNullableType) {
            $propertyType = '?' . $propertyType;
        }

        $this->docBlockAnalyzer->removeTagFromNode($node, 'var');

        $node->setAttribute(self::PHP74_PROPERTY_TYPE, $propertyType);

        // invoke the print, because only attribute has changed
        $node->setAttribute(Attribute::ORIGINAL_NODE, null);

        return $node;
    }

    /**
     * @param string[] $varTypes
     */
    private function getPropertyTypeWithoutNull(array $varTypes): string
    {
        if ($this->isNullableType) {
            $nullTypePosition = array_search('null', $varTypes, true);
            unset($varTypes[$nullTypePosition]);
        }

        return (string) array_pop($varTypes);
    }

    private function shortenLongType(string $type): string
    {
        if ($type === 'boolean') {
            return 'bool';
        }

        if ($type === 'integer') {
            return 'int';
        }

        return $type;
    }

    private function matchesDocTypeAndDefaultValueType(string $propertyType, Property $propertyNode): bool
    {
        $defaultValueNode = $propertyNode->props[0]->default;
        if ($defaultValueNode === null) {
            return true;
        }

        if (! isset($this->typeNameToAllowedDefaultNodeType[$propertyType])) {
            return true;
        }

        if ($this->isNullableType) {
            // is default value "null"?
            return $this->isContantWithValue($defaultValueNode, 'null');
        }

        return $this->matchesDefaultValueToExpectedNodeTypes($propertyType, $defaultValueNode);
    }

    /**
     * @param string[]|string $value
     */
    private function isContantWithValue(Node $node, $value): bool
    {
        return $node instanceof ConstFetch && in_array((string) $node->name, (array) $value, true);
    }

    private function matchesDefaultValueToExpectedNodeTypes(string $propertyType, Node $defaultValueNode): bool
    {
        $allowedDefaultNodeTypes = $this->typeNameToAllowedDefaultNodeType[$propertyType];

        foreach ($allowedDefaultNodeTypes as $allowedDefaultNodeType) {
            if (is_a($defaultValueNode, $allowedDefaultNodeType, true)) {
                if ($propertyType === 'bool') {
                    // make sure it's the right constant value
                    return $this->isContantWithValue($defaultValueNode, ['true', 'false']);
                }

                return true;
            }
        }

        return false;
    }
}
