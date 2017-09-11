<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Transformer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Deprecation\ClassDeprecation;

final class MessageToDeprecationTransformer
{
    public function transform(string $message, Node $node): DeprecationInterface
    {
        if ($node instanceof Class_) {
            return new ClassDeprecation(
                $node->namespacedName->toString(),
                $this->determineNewClass($message)
            );
        }
    }

    /**
     * Matches:
     * - Use <class> instead
     * - Use the <class> instead
     * - Use the <class> class instead
     * - use the <class> class instead
     */
    private function determineNewClass(string $message): string
    {
        $matches = Strings::match($message, '#use( the)? (?<class>[A-Za-z\\\\]+)( class)? instead#i');

        return $matches['class'] ?? '';
    }
}
