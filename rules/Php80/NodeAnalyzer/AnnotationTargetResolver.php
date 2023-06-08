<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\Core\PhpParser\Node\NodeFactory;
final class AnnotationTargetResolver
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @see https://github.com/doctrine/annotations/blob/e6e7b7d5b45a2f2abc5460cc6396480b2b1d321f/lib/Doctrine/Common/Annotations/Annotation/Target.php#L24-L29
     * @var array<string, string>
     */
    private const TARGET_TO_CONSTANT_MAP = [
        'METHOD' => 'TARGET_METHOD',
        'PROPERTY' => 'TARGET_PROPERTY',
        'CLASS' => 'TARGET_CLASS',
        'FUNCTION' => 'TARGET_FUNCTION',
        'ALL' => 'TARGET_ALL',
        // special case
        'ANNOTATION' => 'TARGET_CLASS',
    ];
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param ArrayItemNode[] $targetValues
     * @return ClassConstFetch[]
     */
    public function resolveFlagClassConstFetches(array $targetValues) : array
    {
        $classConstFetches = [];
        foreach ($targetValues as $targetValue) {
            foreach (self::TARGET_TO_CONSTANT_MAP as $target => $constant) {
                if (!$targetValue->value instanceof StringNode) {
                    continue;
                }
                if ($target !== $targetValue->value->value) {
                    continue;
                }
                $classConstFetches[] = $this->nodeFactory->createClassConstFetch('Attribute', $constant);
            }
        }
        return $classConstFetches;
    }
}
