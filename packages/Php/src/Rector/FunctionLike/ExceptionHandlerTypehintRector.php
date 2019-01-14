<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @source https://wiki.php.net/rfc/typed_properties_v2#proposal
 */
final class ExceptionHandlerTypehintRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes property `@var` annotations from annotation to type.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
function handler(Exception $exception) { ... }
set_exception_handler('handler');
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
function handler(Throwable $exception) { ... }
set_exception_handler('handler');
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
        return [Function_::class, ClassMethod::class];
    }

    /**
     * @param Function_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        // exception handle has 1 param exactly
        if (count($node->params) !== 1) {
            return null;
        }

        $paramNode = $node->params[0];
        if ($paramNode->type === null) {
            return null;
        }

        // handle only Exception typehint
        $actualType = $paramNode->type instanceof NullableType ? $this->getName(
            $paramNode->type->type
        ) : $this->getName($paramNode->type);
        if ($actualType !== 'Exception') {
            return null;
        }

        // is probably handling exceptions
        if (! Strings::match((string) $node->name, '#handle#i')) {
            return null;
        }

        if (! $paramNode->type instanceof NullableType) {
            $paramNode->type = new FullyQualified('Throwable');
        } else {
            $paramNode->type->type = new FullyQualified('Throwable');
        }

        return $node;
    }
}
