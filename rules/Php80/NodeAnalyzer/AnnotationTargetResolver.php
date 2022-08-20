<?php

declare (strict_types=1);
namespace Rector\Php80\NodeAnalyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\Core\PhpParser\Node\NodeFactory;
final class AnnotationTargetResolver
{
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
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
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
                if ($target !== $targetValue->value) {
                    continue;
                }
                $classConstFetches[] = $this->nodeFactory->createClassConstFetch('Attribute', $constant);
            }
        }
        return $classConstFetches;
    }
}
