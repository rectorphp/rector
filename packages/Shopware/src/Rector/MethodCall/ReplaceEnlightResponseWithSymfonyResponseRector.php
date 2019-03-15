<?php declare(strict_types=1);

namespace Rector\Shopware\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
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
            ),
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
        if (! $this->isType($node, 'Enlight_Controller_Response_Response')) {
            return null;
        }

        $name = $this->getName($node);
        switch ($name) {
            case 'setHeader':
                return $this->modifySetHeader($node);
            case 'clearHeader':
                return $this->modifyHeader($node, 'remove');
            case 'clearAllHeaders':
                return $this->modifyHeader($node, 'replace');
            case 'clearRawHeaders':
                return $this->modifyHeader($node, 'replace');
            case 'removeCookie':
                return $this->modifyHeader($node, 'removeCookie');
            case 'setRawHeader':
                return $this->modifyRawHeader($node, 'set');
            case 'clearRawHeader':
                return $this->modifyRawHeader($node, 'remove');
            case 'setCookie':
                return $this->modifySetCookie($node);

            default:
                return null;
        }
    }

    private function modifySetHeader(MethodCall $methodCall): ?MethodCall
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        $methodCall->var = new PropertyFetch($methodCall->var, 'headers');
        $methodCall->name = new Identifier('set');

        if (! $methodCall->args[0]->value instanceof String_) {
            return $methodCall;
        }

        /** @var String_ $arg1 */
        $arg1 = $methodCall->args[0]->value;
        $arg1->value = strtolower($arg1->value);

        // We have a cache-control call without replace header (3rd argument)
        if ($arg1->value === 'cache-control' && ! isset($methodCall->args[2])) {
            $methodCall->args[2] = new Arg(new ConstFetch(new Name(['true'])));
        }

        return $methodCall;
    }

    private function modifyHeader(MethodCall $methodCall, string $newMethodName): ?MethodCall
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        $methodCall->var = new PropertyFetch($methodCall->var, 'headers');
        $methodCall->name = new Identifier($newMethodName);

        return $methodCall;
    }

    private function modifyRawHeader(MethodCall $methodCall, string $newMethodName): ?MethodCall
    {
        $methodCall->var = new PropertyFetch($methodCall->var, 'headers');

        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        $methodCall->name = new Identifier($newMethodName);

        if ($methodCall->args[0]->value instanceof String_) {
            $parts = $this->getRawHeaderParts($methodCall->args[0]->value->value);

            $args = [];
            foreach ($parts as $i => $part) {
                if ($i === 0) {
                    $part = strtolower($part);
                }

                $args[] = new Arg(new String_($part));
            }

            $methodCall->args = $args;
        }

        return $methodCall;
    }

    private function modifySetCookie(MethodCall $methodCall): ?MethodCall
    {
        if (! $methodCall->name instanceof Identifier) {
            return null;
        }

        $methodCall->var = new PropertyFetch($methodCall->var, 'headers');
        $methodCall->name = new Identifier('setCookie');

        $new = new New_(new FullyQualified('Symfony\Component\HttpFoundation\Cookie'), $methodCall->args);
        $methodCall->args = [new Arg($new)];

        return $methodCall;
    }

    /**
     * @return string[]
     */
    private function getRawHeaderParts(string $name): array
    {
        return array_map('trim', explode(':', $name, 2));
    }
}
