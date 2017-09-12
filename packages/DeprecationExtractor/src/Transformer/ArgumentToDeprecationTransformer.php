<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Transformer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Scalar\MagicConst\Namespace_;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Deprecation\ClassMethodDeprecation;
use Rector\DeprecationExtractor\RegExp\ClassAndMethodMatcher;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;

final class ArgumentToDeprecationTransformer
{
    /**
     * @var ClassAndMethodMatcher
     */
    private $classAndMethodMatcher;

    /**
     * @var Standard
     */
    private $standardPrinter;

    public function __construct(ClassAndMethodMatcher $classAndMethodMatcher, Standard $standardPrinter)
    {
        $this->classAndMethodMatcher = $classAndMethodMatcher;
        $this->standardPrinter = $standardPrinter;
    }

    /**
     * Probably resolve by recursion, similar too
     * @see \Rector\NodeTypeResolver\NodeVisitor\TypeResolver::__construct()
     */
    public function transform(Arg $argNode): DeprecationInterface
    {
        $message = '';
        if ($argNode->value instanceof Concat) {
            $message .= $this->processConcatNode($argNode->value->left);
            $message .= $this->processConcatNode($argNode->value->right);
        } elseif ($argNode->value instanceof FuncCall) {
            if ((string) $argNode->value->name === 'sprintf') {
                $message = $this->processSprintfNode($argNode->value);

                dump($message);
                die;
            }
        }

        if ($message === '') {
            throw new NotImplementedException(sprintf(
                'Not implemented yet. Go to "%s()" and add check for "%s" node.',
                __METHOD__,
                get_class($argNode->value)
            ));
        }

        return $this->createFromMesssage($message);
    }

    public function tryToCreateClassMethodDeprecation(string $oldMessage, string $newMessage): ?DeprecationInterface
    {
        $oldMethod = $this->classAndMethodMatcher->matchClassWithMethod($oldMessage);
        $newMethod = $this->classAndMethodMatcher->matchClassWithMethod($newMessage);

        return new ClassMethodDeprecation($oldMethod, $newMethod);
    }

    private function processConcatNode(Node $node): string
    {
        if ($node instanceof Method) {
            $classMethodNode = $node->getAttribute(Attribute::SCOPE_NODE);

            return $node->getAttribute(Attribute::CLASS_NAME) . '::' . $classMethodNode->name->name;
        }

        if ($node instanceof String_) {
            $message = $node->value; // complet class to local methods
            return $this->completeClassToLocalMethods($message, (string) $node->getAttribute(Attribute::CLASS_NAME));
        }

        if ($node instanceof Concat) {
            $message = $this->processConcatNode($node->left);
            $message .= $this->processConcatNode($node->right);

            return $message;
        }

        if ($node instanceof Namespace_) {
            return (string) $node->getAttribute(Attribute::NAMESPACE);
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" node.',
            __METHOD__,
            get_class($node)
        ));
    }

    private function completeClassToLocalMethods(string $message, string $class): string
    {
        $completeMessage = '';
        $words = explode(' ', $message);

        foreach ($words as $word) {
            $completeMessage .= ' ' . $this->prependClassToMethodCallIfNeeded($word, $class);
        }

        return trim($completeMessage);
    }

    private function prependClassToMethodCallIfNeeded(string $word, string $class): string
    {
        // is method()
        if (Strings::endsWith($word, '()') && strlen($word) > 2) {
            // doesn't include class in the beggning
            if (! Strings::startsWith($word, $class)) {
                return $class . '::' . $word;
            }
        }

        // is method('...')
        if (Strings::endsWith($word, '\')')) {
            // doesn't include class in the beggning
            if (! Strings::startsWith($word, $class)) {
                return $class . '::' . $word;
            }
        }

        return $word;
    }

    private function createFromMesssage(string $message): DeprecationInterface
    {
        $result = Strings::split($message, '#^use |Use#');
        if (count($result) === 2) {
            [$oldMessage, $newMessage] = $result;
            $deprecation = $this->tryToCreateClassMethodDeprecation($oldMessage, $newMessage);
            if ($deprecation) {
                return $deprecation;
            }
        }

        dump($message);
        die;

        throw new NotImplementedException(sprintf(
            '%s() did not resolve "%s" messsage, so %s was not created. Implement it.',
            __METHOD__,
            $message,
            DeprecationInterface::class
        ));
    }

    private function processSprintfNode(FuncCall $funcCallNode): string
    {
        dump($funcCallNode);
        die;
    }
}
