<?php declare(strict_types=1);

namespace Rector\Shopware\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ReplaceEnlightResponseWithSymfonyResponseRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace Enlight Response methods with Symfony Response methods', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class FrontendController extends \Enlight_Controller_Action
{
    public function run()
    {
        $this->Response()->setHeader('Foo', 'Yea');
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class FrontendController extends \Enlight_Controller_Action
{
    public function run()
    {
        $this->Response()->headers->set('Foo', 'Yea');
    }
}
CODE_SAMPLE

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isType($node, 'Enlight_Controller_Response_Response')) {
            return null;
        }

        if (!$node->name instanceof Node\Identifier) {
            return null;
        }

        switch ($node->name->name) {
            case 'setHeader':
                return self::modifySetHeader($node);
            case 'clearHeader':
                return self::modifyHeader($node, 'remove');
            case 'clearAllHeaders':
                return self::modifyHeader($node, 'replace');
            case 'clearRawHeaders':
                return self::modifyHeader($node, 'replace');
            case 'removeCookie':
                return self::modifyHeader($node, 'removeCookie');
            case 'setRawHeader':
                return self::modifyRawHeader($node, 'set');
            case 'clearRawHeader':
                return self::modifyRawHeader($node, 'remove');
            case 'setCookie':
                return self::modifySetCookie($node);

            default:
                return null;
        }
    }

    private static function modifySetHeader(MethodCall $node): ?MethodCall
    {
        if (!$node->name instanceof Node\Identifier) {
            return null;
        }

        $node->var = new Node\Expr\PropertyFetch($node->var, 'headers');
        $node->name->name = 'set';

        if (! $node->args[0]->value instanceof Node\Scalar\String_) {
            return $node;
        }

        /** @var Node\Scalar\String_ $arg1 */
        $arg1 = $node->args[0]->value;
        $arg1->value = strtolower($arg1->value);

        // We have a cache-control call without replace header (3rd argument)
        if ($arg1->value === 'cache-control' && !isset($node->args[2])) {
            $node->args[2] = new Node\Arg(new Node\Expr\ConstFetch(new Node\Name(['true'])));
        }

        return $node;
    }

    private static function modifyHeader(MethodCall $node, string $newMethodName): ?MethodCall
    {
        if (!$node->name instanceof Node\Identifier) {
            return null;
        }

        $node->var = new Node\Expr\PropertyFetch($node->var, 'headers');
        $node->name->name = $newMethodName;

        return $node;
    }

    private static function modifyRawHeader(MethodCall $node, string $newMethodName): ?MethodCall
    {
        $node->var = new Node\Expr\PropertyFetch($node->var, 'headers');

        if (!$node->name instanceof Node\Identifier) {
            return null;
        }

        $node->name->name = $newMethodName;

        if ($node->args[0]->value instanceof Node\Scalar\String_) {
            $parts = self::getRawHeaderParts($node->args[0]->value->value);

            $args = [];

            foreach ($parts as $i => $part) {
                if ($i === 0) {
                    $part = strtolower($part);
                }

                $args[] = new Node\Arg(
                    new Node\Scalar\String_($part)
                );
            }

            $node->args = $args;
        }


        return $node;
    }

    private static function modifySetCookie(MethodCall $node): ?MethodCall
    {
        if (!$node->name instanceof Node\Identifier) {
            return null;
        }

        $node->var = new Node\Expr\PropertyFetch($node->var, 'headers');
        $node->name->name = 'setCookie';

        $node->args = [
            new Node\Arg(
                new Node\Expr\New_(
                    new Node\Name('\\Symfony\\Component\\HttpFoundation\\Cookie'),
                    $node->args
                )
            )
        ];

        return $node;
    }


    /**
     * @return string[]
     */
    private static function getRawHeaderParts(string $name): array
    {
        return array_map('trim', explode(':', $name, 2));
    }
}
