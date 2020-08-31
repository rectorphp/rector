<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * @see https://wiki.php.net/rfc/multiple-catch
 * @see \Rector\Php71\Tests\Rector\TryCatch\MultiExceptionCatchRector\MultiExceptionCatchRectorTest
 */
final class MultiExceptionCatchRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes multi catch of same exception to single one | separated.',
            [
                new CodeSample(
<<<'PHP'
try {
   // Some code...
} catch (ExceptionType1 $exception) {
   $sameCode;
} catch (ExceptionType2 $exception) {
   $sameCode;
}
PHP
                    ,
<<<'PHP'
try {
   // Some code...
} catch (ExceptionType1 | ExceptionType2 $exception) {
   $sameCode;
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [TryCatch::class];
    }

    /**
     * @param TryCatch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::MULTI_EXCEPTION_CATCH)) {
            return null;
        }

        if (count($node->catches) < 2) {
            return null;
        }

        $catchKeysByContent = $this->collectCatchKeysByContent($node);

        foreach ($catchKeysByContent as $keys) {
            // no duplicates
            if (count($keys) < 2) {
                continue;
            }

            $collectedTypes = $this->collectTypesFromCatchedByIds($node, $keys);

            $firstTryKey = array_shift($keys);
            $node->catches[$firstTryKey]->types = $collectedTypes;

            foreach ($keys as $key) {
                unset($node->catches[$key]);
            }
        }

        return $node;
    }

    /**
     * @return array<string, array<int|string>>
     */
    private function collectCatchKeysByContent(TryCatch $tryCatch): array
    {
        $catchKeysByContent = [];
        foreach ($tryCatch->catches as $key => $catch) {
            $catchContent = $this->print($catch->stmts);
            /** @var int $key */
            $catchKeysByContent[$catchContent][] = $key;
        }

        return $catchKeysByContent;
    }

    /**
     * @param int[] $keys
     * @return Name[]
     */
    private function collectTypesFromCatchedByIds(TryCatch $tryCatch, array $keys): array
    {
        $collectedTypes = [];

        foreach ($keys as $key) {
            $collectedTypes = array_merge($collectedTypes, $tryCatch->catches[$key]->types);
        }

        return $collectedTypes;
    }
}
