<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Printer;

use LogicException;
use PHPStan\PhpDocParser\Ast\Attribute;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprArrayNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\AssertTagMethodValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\AssertTagPropertyValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\AssertTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineAnnotation;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineArgument;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineArray;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineArrayItem;
use PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ExtendsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ImplementsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MixinTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamOutTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\SelfOutTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasImportTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\TypeAliasTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\UsesTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeForParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\InvalidTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ObjectShapeItemNode;
use PHPStan\PhpDocParser\Ast\Type\ObjectShapeNode;
use PHPStan\PhpDocParser\Ast\Type\OffsetAccessTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use function array_keys;
use function array_map;
use function count;
use function get_class;
use function get_object_vars;
use function implode;
use function in_array;
use function is_array;
use function preg_match_all;
use function sprintf;
use function strlen;
use function strpos;
use function trim;
use const PREG_SET_ORDER;
/**
 * Inspired by https://github.com/nikic/PHP-Parser/tree/36a6dcd04e7b0285e8f0868f44bd4927802f7df1
 *
 * Copyright (c) 2011, Nikita Popov
 * All rights reserved.
 */
final class Printer
{
    /** @var Differ<Node> */
    private $differ;
    /**
     * Map From "{$class}->{$subNode}" to string that should be inserted
     * between elements of this list subnode
     *
     * @var array<string, string>
     */
    private $listInsertionMap = [PhpDocNode::class . '->children' => "\n * ", UnionTypeNode::class . '->types' => '|', IntersectionTypeNode::class . '->types' => '&', ArrayShapeNode::class . '->items' => ', ', ObjectShapeNode::class . '->items' => ', ', CallableTypeNode::class . '->parameters' => ', ', GenericTypeNode::class . '->genericTypes' => ', ', ConstExprArrayNode::class . '->items' => ', ', MethodTagValueNode::class . '->parameters' => ', ', DoctrineArray::class . '->items' => ', ', DoctrineAnnotation::class . '->arguments' => ', '];
    /**
     * [$find, $extraLeft, $extraRight]
     *
     * @var array<string, array{string|null, string, string}>
     */
    private $emptyListInsertionMap = [CallableTypeNode::class . '->parameters' => ['(', '', ''], ArrayShapeNode::class . '->items' => ['{', '', ''], ObjectShapeNode::class . '->items' => ['{', '', ''], DoctrineArray::class . '->items' => ['{', '', ''], DoctrineAnnotation::class . '->arguments' => ['(', '', '']];
    /** @var array<string, list<class-string<TypeNode>>> */
    private $parenthesesMap = [CallableTypeNode::class . '->returnType' => [CallableTypeNode::class, UnionTypeNode::class, IntersectionTypeNode::class], ArrayTypeNode::class . '->type' => [CallableTypeNode::class, UnionTypeNode::class, IntersectionTypeNode::class, ConstTypeNode::class, NullableTypeNode::class], OffsetAccessTypeNode::class . '->type' => [CallableTypeNode::class, UnionTypeNode::class, IntersectionTypeNode::class, ConstTypeNode::class, NullableTypeNode::class]];
    /** @var array<string, list<class-string<TypeNode>>> */
    private $parenthesesListMap = [IntersectionTypeNode::class . '->types' => [IntersectionTypeNode::class, UnionTypeNode::class, NullableTypeNode::class], UnionTypeNode::class . '->types' => [IntersectionTypeNode::class, UnionTypeNode::class, NullableTypeNode::class]];
    public function printFormatPreserving(PhpDocNode $node, PhpDocNode $originalNode, TokenIterator $originalTokens) : string
    {
        $this->differ = new \PHPStan\PhpDocParser\Printer\Differ(static function ($a, $b) {
            if ($a instanceof Node && $b instanceof Node) {
                return $a === $b->getAttribute(Attribute::ORIGINAL_NODE);
            }
            return \false;
        });
        $tokenIndex = 0;
        $result = $this->printArrayFormatPreserving($node->children, $originalNode->children, $originalTokens, $tokenIndex, PhpDocNode::class, 'children');
        if ($result !== null) {
            return $result . $originalTokens->getContentBetween($tokenIndex, $originalTokens->getTokenCount());
        }
        return $this->print($node);
    }
    public function print(Node $node) : string
    {
        if ($node instanceof PhpDocNode) {
            return "/**\n *" . implode("\n *", array_map(function (PhpDocChildNode $child) : string {
                $s = $this->print($child);
                return $s === '' ? '' : ' ' . $s;
            }, $node->children)) . "\n */";
        }
        if ($node instanceof PhpDocTextNode) {
            return $node->text;
        }
        if ($node instanceof PhpDocTagNode) {
            if ($node->value instanceof DoctrineTagValueNode) {
                return $this->print($node->value);
            }
            return trim(sprintf('%s %s', $node->name, $this->print($node->value)));
        }
        if ($node instanceof PhpDocTagValueNode) {
            return $this->printTagValue($node);
        }
        if ($node instanceof TypeNode) {
            return $this->printType($node);
        }
        if ($node instanceof ConstExprNode) {
            return $this->printConstExpr($node);
        }
        if ($node instanceof MethodTagValueParameterNode) {
            $type = $node->type !== null ? $this->print($node->type) . ' ' : '';
            $isReference = $node->isReference ? '&' : '';
            $isVariadic = $node->isVariadic ? '...' : '';
            $default = $node->defaultValue !== null ? ' = ' . $this->print($node->defaultValue) : '';
            return "{$type}{$isReference}{$isVariadic}{$node->parameterName}{$default}";
        }
        if ($node instanceof CallableTypeParameterNode) {
            $type = $this->print($node->type) . ' ';
            $isReference = $node->isReference ? '&' : '';
            $isVariadic = $node->isVariadic ? '...' : '';
            $isOptional = $node->isOptional ? '=' : '';
            return trim("{$type}{$isReference}{$isVariadic}{$node->parameterName}") . $isOptional;
        }
        if ($node instanceof DoctrineAnnotation) {
            return (string) $node;
        }
        if ($node instanceof DoctrineArgument) {
            return (string) $node;
        }
        if ($node instanceof DoctrineArray) {
            return (string) $node;
        }
        if ($node instanceof DoctrineArrayItem) {
            return (string) $node;
        }
        throw new LogicException(sprintf('Unknown node type %s', get_class($node)));
    }
    private function printTagValue(PhpDocTagValueNode $node) : string
    {
        // only nodes that contain another node are handled here
        // the rest falls back on (string) $node
        if ($node instanceof AssertTagMethodValueNode) {
            $isNegated = $node->isNegated ? '!' : '';
            $isEquality = $node->isEquality ? '=' : '';
            $type = $this->printType($node->type);
            return trim("{$isNegated}{$isEquality}{$type} {$node->parameter}->{$node->method}() {$node->description}");
        }
        if ($node instanceof AssertTagPropertyValueNode) {
            $isNegated = $node->isNegated ? '!' : '';
            $isEquality = $node->isEquality ? '=' : '';
            $type = $this->printType($node->type);
            return trim("{$isNegated}{$isEquality}{$type} {$node->parameter}->{$node->property} {$node->description}");
        }
        if ($node instanceof AssertTagValueNode) {
            $isNegated = $node->isNegated ? '!' : '';
            $isEquality = $node->isEquality ? '=' : '';
            $type = $this->printType($node->type);
            return trim("{$isNegated}{$isEquality}{$type} {$node->parameter} {$node->description}");
        }
        if ($node instanceof ExtendsTagValueNode || $node instanceof ImplementsTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$type} {$node->description}");
        }
        if ($node instanceof MethodTagValueNode) {
            $static = $node->isStatic ? 'static ' : '';
            $returnType = $node->returnType !== null ? $this->printType($node->returnType) . ' ' : '';
            $parameters = implode(', ', array_map(function (MethodTagValueParameterNode $parameter) : string {
                return $this->print($parameter);
            }, $node->parameters));
            $description = $node->description !== '' ? " {$node->description}" : '';
            $templateTypes = count($node->templateTypes) > 0 ? '<' . implode(', ', array_map(function (TemplateTagValueNode $templateTag) : string {
                return $this->print($templateTag);
            }, $node->templateTypes)) . '>' : '';
            return "{$static}{$returnType}{$node->methodName}{$templateTypes}({$parameters}){$description}";
        }
        if ($node instanceof MixinTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$type} {$node->description}");
        }
        if ($node instanceof ParamOutTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$type} {$node->parameterName} {$node->description}");
        }
        if ($node instanceof ParamTagValueNode) {
            $reference = $node->isReference ? '&' : '';
            $variadic = $node->isVariadic ? '...' : '';
            $type = $this->printType($node->type);
            return trim("{$type} {$reference}{$variadic}{$node->parameterName} {$node->description}");
        }
        if ($node instanceof PropertyTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$type} {$node->propertyName} {$node->description}");
        }
        if ($node instanceof ReturnTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$type} {$node->description}");
        }
        if ($node instanceof SelfOutTagValueNode) {
            $type = $this->printType($node->type);
            return trim($type . ' ' . $node->description);
        }
        if ($node instanceof TemplateTagValueNode) {
            $bound = $node->bound !== null ? ' of ' . $this->printType($node->bound) : '';
            $default = $node->default !== null ? ' = ' . $this->printType($node->default) : '';
            return trim("{$node->name}{$bound}{$default} {$node->description}");
        }
        if ($node instanceof ThrowsTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$type} {$node->description}");
        }
        if ($node instanceof TypeAliasImportTagValueNode) {
            return trim("{$node->importedAlias} from " . $this->printType($node->importedFrom) . ($node->importedAs !== null ? " as {$node->importedAs}" : ''));
        }
        if ($node instanceof TypeAliasTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$node->alias} {$type}");
        }
        if ($node instanceof UsesTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$type} {$node->description}");
        }
        if ($node instanceof VarTagValueNode) {
            $type = $this->printType($node->type);
            return trim("{$type} " . trim("{$node->variableName} {$node->description}"));
        }
        return (string) $node;
    }
    private function printType(TypeNode $node) : string
    {
        if ($node instanceof ArrayShapeNode) {
            $items = array_map(function (ArrayShapeItemNode $item) : string {
                return $this->printType($item);
            }, $node->items);
            if (!$node->sealed) {
                $items[] = '...';
            }
            return $node->kind . '{' . implode(', ', $items) . '}';
        }
        if ($node instanceof ArrayShapeItemNode) {
            if ($node->keyName !== null) {
                return sprintf('%s%s: %s', $this->print($node->keyName), $node->optional ? '?' : '', $this->printType($node->valueType));
            }
            return $this->printType($node->valueType);
        }
        if ($node instanceof ArrayTypeNode) {
            return $this->printOffsetAccessType($node->type) . '[]';
        }
        if ($node instanceof CallableTypeNode) {
            if ($node->returnType instanceof CallableTypeNode || $node->returnType instanceof UnionTypeNode || $node->returnType instanceof IntersectionTypeNode) {
                $returnType = $this->wrapInParentheses($node->returnType);
            } else {
                $returnType = $this->printType($node->returnType);
            }
            $parameters = implode(', ', array_map(function (CallableTypeParameterNode $parameterNode) : string {
                return $this->print($parameterNode);
            }, $node->parameters));
            return "{$node->identifier}({$parameters}): {$returnType}";
        }
        if ($node instanceof ConditionalTypeForParameterNode) {
            return sprintf('(%s %s %s ? %s : %s)', $node->parameterName, $node->negated ? 'is not' : 'is', $this->printType($node->targetType), $this->printType($node->if), $this->printType($node->else));
        }
        if ($node instanceof ConditionalTypeNode) {
            return sprintf('(%s %s %s ? %s : %s)', $this->printType($node->subjectType), $node->negated ? 'is not' : 'is', $this->printType($node->targetType), $this->printType($node->if), $this->printType($node->else));
        }
        if ($node instanceof ConstTypeNode) {
            return $this->printConstExpr($node->constExpr);
        }
        if ($node instanceof GenericTypeNode) {
            $genericTypes = [];
            foreach ($node->genericTypes as $index => $type) {
                $variance = $node->variances[$index] ?? GenericTypeNode::VARIANCE_INVARIANT;
                if ($variance === GenericTypeNode::VARIANCE_INVARIANT) {
                    $genericTypes[] = $this->printType($type);
                } elseif ($variance === GenericTypeNode::VARIANCE_BIVARIANT) {
                    $genericTypes[] = '*';
                } else {
                    $genericTypes[] = sprintf('%s %s', $variance, $this->print($type));
                }
            }
            return $node->type . '<' . implode(', ', $genericTypes) . '>';
        }
        if ($node instanceof IdentifierTypeNode) {
            return $node->name;
        }
        if ($node instanceof IntersectionTypeNode || $node instanceof UnionTypeNode) {
            $items = [];
            foreach ($node->types as $type) {
                if ($type instanceof IntersectionTypeNode || $type instanceof UnionTypeNode || $type instanceof NullableTypeNode) {
                    $items[] = $this->wrapInParentheses($type);
                    continue;
                }
                $items[] = $this->printType($type);
            }
            return implode($node instanceof IntersectionTypeNode ? '&' : '|', $items);
        }
        if ($node instanceof InvalidTypeNode) {
            return (string) $node;
        }
        if ($node instanceof NullableTypeNode) {
            if ($node->type instanceof IntersectionTypeNode || $node->type instanceof UnionTypeNode) {
                return '?(' . $this->printType($node->type) . ')';
            }
            return '?' . $this->printType($node->type);
        }
        if ($node instanceof ObjectShapeNode) {
            $items = array_map(function (ObjectShapeItemNode $item) : string {
                return $this->printType($item);
            }, $node->items);
            return 'object{' . implode(', ', $items) . '}';
        }
        if ($node instanceof ObjectShapeItemNode) {
            if ($node->keyName !== null) {
                return sprintf('%s%s: %s', $this->print($node->keyName), $node->optional ? '?' : '', $this->printType($node->valueType));
            }
            return $this->printType($node->valueType);
        }
        if ($node instanceof OffsetAccessTypeNode) {
            return $this->printOffsetAccessType($node->type) . '[' . $this->printType($node->offset) . ']';
        }
        if ($node instanceof ThisTypeNode) {
            return (string) $node;
        }
        throw new LogicException(sprintf('Unknown node type %s', get_class($node)));
    }
    private function wrapInParentheses(TypeNode $node) : string
    {
        return '(' . $this->printType($node) . ')';
    }
    private function printOffsetAccessType(TypeNode $type) : string
    {
        if ($type instanceof CallableTypeNode || $type instanceof UnionTypeNode || $type instanceof IntersectionTypeNode || $type instanceof ConstTypeNode || $type instanceof NullableTypeNode) {
            return $this->wrapInParentheses($type);
        }
        return $this->printType($type);
    }
    private function printConstExpr(ConstExprNode $node) : string
    {
        // this is fine - ConstExprNode classes do not contain nodes that need smart printer logic
        return (string) $node;
    }
    /**
     * @param Node[] $nodes
     * @param Node[] $originalNodes
     */
    private function printArrayFormatPreserving(array $nodes, array $originalNodes, TokenIterator $originalTokens, int &$tokenIndex, string $parentNodeClass, string $subNodeName) : ?string
    {
        $diff = $this->differ->diffWithReplacements($originalNodes, $nodes);
        $mapKey = $parentNodeClass . '->' . $subNodeName;
        $insertStr = $this->listInsertionMap[$mapKey] ?? null;
        $result = '';
        $beforeFirstKeepOrReplace = \true;
        $delayedAdd = [];
        $insertNewline = \false;
        [$isMultiline, $beforeAsteriskIndent, $afterAsteriskIndent] = $this->isMultiline($tokenIndex, $originalNodes, $originalTokens);
        if ($insertStr === "\n * ") {
            $insertStr = sprintf('%s%s*%s', $originalTokens->getDetectedNewline() ?? "\n", $beforeAsteriskIndent, $afterAsteriskIndent);
        }
        foreach ($diff as $i => $diffElem) {
            $diffType = $diffElem->type;
            $newNode = $diffElem->new;
            $originalNode = $diffElem->old;
            if ($diffType === \PHPStan\PhpDocParser\Printer\DiffElem::TYPE_KEEP || $diffType === \PHPStan\PhpDocParser\Printer\DiffElem::TYPE_REPLACE) {
                $beforeFirstKeepOrReplace = \false;
                if (!$newNode instanceof Node || !$originalNode instanceof Node) {
                    return null;
                }
                $itemStartPos = $originalNode->getAttribute(Attribute::START_INDEX);
                $itemEndPos = $originalNode->getAttribute(Attribute::END_INDEX);
                if ($itemStartPos < 0 || $itemEndPos < 0 || $itemStartPos < $tokenIndex) {
                    throw new LogicException();
                }
                $result .= $originalTokens->getContentBetween($tokenIndex, $itemStartPos);
                if (count($delayedAdd) > 0) {
                    foreach ($delayedAdd as $delayedAddNode) {
                        $parenthesesNeeded = isset($this->parenthesesListMap[$mapKey]) && in_array(get_class($delayedAddNode), $this->parenthesesListMap[$mapKey], \true);
                        if ($parenthesesNeeded) {
                            $result .= '(';
                        }
                        $result .= $this->printNodeFormatPreserving($delayedAddNode, $originalTokens);
                        if ($parenthesesNeeded) {
                            $result .= ')';
                        }
                        if ($insertNewline) {
                            $result .= $insertStr . sprintf('%s%s*%s', $originalTokens->getDetectedNewline() ?? "\n", $beforeAsteriskIndent, $afterAsteriskIndent);
                        } else {
                            $result .= $insertStr;
                        }
                    }
                    $delayedAdd = [];
                }
                $parenthesesNeeded = isset($this->parenthesesListMap[$mapKey]) && in_array(get_class($newNode), $this->parenthesesListMap[$mapKey], \true) && !in_array(get_class($originalNode), $this->parenthesesListMap[$mapKey], \true);
                $addParentheses = $parenthesesNeeded && !$originalTokens->hasParentheses($itemStartPos, $itemEndPos);
                if ($addParentheses) {
                    $result .= '(';
                }
                $result .= $this->printNodeFormatPreserving($newNode, $originalTokens);
                if ($addParentheses) {
                    $result .= ')';
                }
                $tokenIndex = $itemEndPos + 1;
            } elseif ($diffType === \PHPStan\PhpDocParser\Printer\DiffElem::TYPE_ADD) {
                if ($insertStr === null) {
                    return null;
                }
                if (!$newNode instanceof Node) {
                    return null;
                }
                if ($insertStr === ', ' && $isMultiline) {
                    $insertStr = ',';
                    $insertNewline = \true;
                }
                if ($beforeFirstKeepOrReplace) {
                    // Will be inserted at the next "replace" or "keep" element
                    $delayedAdd[] = $newNode;
                    continue;
                }
                $itemEndPos = $tokenIndex - 1;
                if ($insertNewline) {
                    $result .= $insertStr . sprintf('%s%s*%s', $originalTokens->getDetectedNewline() ?? "\n", $beforeAsteriskIndent, $afterAsteriskIndent);
                } else {
                    $result .= $insertStr;
                }
                $parenthesesNeeded = isset($this->parenthesesListMap[$mapKey]) && in_array(get_class($newNode), $this->parenthesesListMap[$mapKey], \true);
                if ($parenthesesNeeded) {
                    $result .= '(';
                }
                $result .= $this->printNodeFormatPreserving($newNode, $originalTokens);
                if ($parenthesesNeeded) {
                    $result .= ')';
                }
                $tokenIndex = $itemEndPos + 1;
            } elseif ($diffType === \PHPStan\PhpDocParser\Printer\DiffElem::TYPE_REMOVE) {
                if (!$originalNode instanceof Node) {
                    return null;
                }
                $itemStartPos = $originalNode->getAttribute(Attribute::START_INDEX);
                $itemEndPos = $originalNode->getAttribute(Attribute::END_INDEX);
                if ($itemStartPos < 0 || $itemEndPos < 0) {
                    throw new LogicException();
                }
                if ($i === 0) {
                    // If we're removing from the start, keep the tokens before the node and drop those after it,
                    // instead of the other way around.
                    $originalTokensArray = $originalTokens->getTokens();
                    for ($j = $tokenIndex; $j < $itemStartPos; $j++) {
                        if ($originalTokensArray[$j][Lexer::TYPE_OFFSET] === Lexer::TOKEN_PHPDOC_EOL) {
                            break;
                        }
                        $result .= $originalTokensArray[$j][Lexer::VALUE_OFFSET];
                    }
                }
                $tokenIndex = $itemEndPos + 1;
            }
        }
        if (count($delayedAdd) > 0) {
            if (!isset($this->emptyListInsertionMap[$mapKey])) {
                return null;
            }
            [$findToken, $extraLeft, $extraRight] = $this->emptyListInsertionMap[$mapKey];
            if ($findToken !== null) {
                $originalTokensArray = $originalTokens->getTokens();
                for (; $tokenIndex < count($originalTokensArray); $tokenIndex++) {
                    $result .= $originalTokensArray[$tokenIndex][Lexer::VALUE_OFFSET];
                    if ($originalTokensArray[$tokenIndex][Lexer::VALUE_OFFSET] !== $findToken) {
                        continue;
                    }
                    $tokenIndex++;
                    break;
                }
            }
            $first = \true;
            $result .= $extraLeft;
            foreach ($delayedAdd as $delayedAddNode) {
                if (!$first) {
                    $result .= $insertStr;
                    if ($insertNewline) {
                        $result .= sprintf('%s%s*%s', $originalTokens->getDetectedNewline() ?? "\n", $beforeAsteriskIndent, $afterAsteriskIndent);
                    }
                }
                $result .= $this->printNodeFormatPreserving($delayedAddNode, $originalTokens);
                $first = \false;
            }
            $result .= $extraRight;
        }
        return $result;
    }
    /**
     * @param Node[] $nodes
     * @return array{bool, string, string}
     */
    private function isMultiline(int $initialIndex, array $nodes, TokenIterator $originalTokens) : array
    {
        $isMultiline = count($nodes) > 1;
        $pos = $initialIndex;
        $allText = '';
        /** @var Node|null $node */
        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }
            $endPos = $node->getAttribute(Attribute::END_INDEX) + 1;
            $text = $originalTokens->getContentBetween($pos, $endPos);
            $allText .= $text;
            if (strpos($text, "\n") === \false) {
                // We require that a newline is present between *every* item. If the formatting
                // is inconsistent, with only some items having newlines, we don't consider it
                // as multiline
                $isMultiline = \false;
            }
            $pos = $endPos;
        }
        $c = preg_match_all('~\\n(?<before>[\\x09\\x20]*)\\*(?<after>\\x20*)~', $allText, $matches, PREG_SET_ORDER);
        if ($c === 0) {
            return [$isMultiline, '', ''];
        }
        $before = '';
        $after = '';
        foreach ($matches as $match) {
            if (strlen($match['before']) > strlen($before)) {
                $before = $match['before'];
            }
            if (strlen($match['after']) <= strlen($after)) {
                continue;
            }
            $after = $match['after'];
        }
        return [$isMultiline, $before, $after];
    }
    private function printNodeFormatPreserving(Node $node, TokenIterator $originalTokens) : string
    {
        /** @var Node|null $originalNode */
        $originalNode = $node->getAttribute(Attribute::ORIGINAL_NODE);
        if ($originalNode === null) {
            return $this->print($node);
        }
        $class = get_class($node);
        if ($class !== get_class($originalNode)) {
            throw new LogicException();
        }
        $startPos = $originalNode->getAttribute(Attribute::START_INDEX);
        $endPos = $originalNode->getAttribute(Attribute::END_INDEX);
        if ($startPos < 0 || $endPos < 0) {
            throw new LogicException();
        }
        $result = '';
        $pos = $startPos;
        $subNodeNames = array_keys(get_object_vars($node));
        foreach ($subNodeNames as $subNodeName) {
            $subNode = $node->{$subNodeName};
            $origSubNode = $originalNode->{$subNodeName};
            if (!$subNode instanceof Node && $subNode !== null || !$origSubNode instanceof Node && $origSubNode !== null) {
                if ($subNode === $origSubNode) {
                    // Unchanged, can reuse old code
                    continue;
                }
                if (is_array($subNode) && is_array($origSubNode)) {
                    // Array subnode changed, we might be able to reconstruct it
                    $listResult = $this->printArrayFormatPreserving($subNode, $origSubNode, $originalTokens, $pos, $class, $subNodeName);
                    if ($listResult === null) {
                        return $this->print($node);
                    }
                    $result .= $listResult;
                    continue;
                }
                return $this->print($node);
            }
            if ($origSubNode === null) {
                if ($subNode === null) {
                    // Both null, nothing to do
                    continue;
                }
                return $this->print($node);
            }
            $subStartPos = $origSubNode->getAttribute(Attribute::START_INDEX);
            $subEndPos = $origSubNode->getAttribute(Attribute::END_INDEX);
            if ($subStartPos < 0 || $subEndPos < 0) {
                throw new LogicException();
            }
            if ($subNode === null) {
                return $this->print($node);
            }
            $result .= $originalTokens->getContentBetween($pos, $subStartPos);
            $mapKey = get_class($node) . '->' . $subNodeName;
            $parenthesesNeeded = isset($this->parenthesesMap[$mapKey]) && in_array(get_class($subNode), $this->parenthesesMap[$mapKey], \true);
            if ($subNode->getAttribute(Attribute::ORIGINAL_NODE) !== null) {
                $parenthesesNeeded = $parenthesesNeeded && !in_array(get_class($subNode->getAttribute(Attribute::ORIGINAL_NODE)), $this->parenthesesMap[$mapKey], \true);
            }
            $addParentheses = $parenthesesNeeded && !$originalTokens->hasParentheses($subStartPos, $subEndPos);
            if ($addParentheses) {
                $result .= '(';
            }
            $result .= $this->printNodeFormatPreserving($subNode, $originalTokens);
            if ($addParentheses) {
                $result .= ')';
            }
            $pos = $subEndPos + 1;
        }
        return $result . $originalTokens->getContentBetween($pos, $endPos + 1);
    }
}
