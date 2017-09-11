<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Transformer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Deprecation\ClassDeprecation;
use Rector\DeprecationExtractor\RegExp\ClassAndMethodMatcher;

final class MessageToDeprecationTransformer
{
    /**
     * @var ClassAndMethodMatcher
     */
    private $classAndMethodMatcher;

    public function __construct(ClassAndMethodMatcher $classAndMethodMatcher)
    {
        $this->classAndMethodMatcher = $classAndMethodMatcher;
    }

    public function transform(string $message, Node $node): DeprecationInterface
    {
        if ($node instanceof Class_) {
            return new ClassDeprecation(
                $node->namespacedName->toString(),
                $this->classAndMethodMatcher->matchClassWithMethodInstead($message)
            );
        }
    }
}
