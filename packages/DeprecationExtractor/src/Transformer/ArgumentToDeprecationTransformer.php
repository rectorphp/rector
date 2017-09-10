<?php declare(strict_types=1);

namespace Rector\DeprecationExtractor\Transformer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\MagicConst\Method;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\DeprecationExtractor\Contract\Deprecation\DeprecationInterface;
use Rector\DeprecationExtractor\Deprecation\ClassMethodDeprecation;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;

final class ArgumentToDeprecationTransformer
{
    public function transform(Arg $argNode): DeprecationInterface
    {
        dump($argNode);
        die;
    }

    /**
     * Probably resolve by recursion, similar too
     * @see \Rector\NodeTypeResolver\NodeVisitor\TypeResolver::__construct()
     */
    public function createFromNode(Node $node, string $scope): DeprecationInterface
    {
        $message = '';
        if ($node instanceof Concat) {
            $message .= $this->processConcatNode($node->left);
            $message .= $this->processConcatNode($node->right);
        }

        return $this->createFromMesssage($message, $scope);
    }

    public function tryToCreateClassMethodDeprecation(string $oldMessage, string $newMessage): ?DeprecationInterface
    {
        // try to find "SomeClass::methodCall()"
        $matches = Strings::matchAll($oldMessage, self::CLASS_WITH_METHOD_PATTERN);
        if (isset($matches[0]['classMethod'])) {
            $oldClassWithMethod = $matches[0]['classMethod'];
        }

        // try to find "SomeClass::methodCall()"
        $matches = Strings::matchAll($newMessage, self::CLASS_WITH_METHOD_PATTERN);
        if (isset($matches[0]['classMethod'])) {
            $newClassWithMethod = $matches[0]['classMethod'];
        }

        if (isset($oldClassWithMethod, $newClassWithMethod)) {
            [$oldClass, $oldMethod] = explode('::', $oldClassWithMethod);
            [$newClass, $newMethod] = explode('::', $newClassWithMethod);

            if ($oldClass === $newClass) {
                // simple method replacement
                return new ClassMethodDeprecation(
                    $oldClass,
                    rtrim($oldMethod, '()'),
                    rtrim($newMethod, '()')
                );
            }
        }

        return null;
    }

    private function processConcatNode(Node $node, string $scope): string
    {
        if ($node instanceof Method) {
            $classMethodNode = $this->findParentOfType($node, ClassMethod::class);

            return $node->getAttribute(Attribute::CLASS_NAME) . '::' . $classMethodNode->name->name;
        }

        if ($node instanceof String_) {
            $message = $node->value; // complet class to local methods
            return $this->completeClassToLocalMethods($message, (string) $node->getAttribute(Attribute::CLASS_NAME));
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" node.',
            __METHOD__,
            get_class($node)
        ));
    }

    private function findParentOfType(Node $node, string $type): Node
    {
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);

        while (! is_a($parentNode, $type, true)) {
            $parentNode = $parentNode->getAttribute(Attribute::PARENT_NODE);
        }

        return $parentNode;
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
        // format: don't use this, use that
        if (Strings::contains($message, ' use ')) {
            [$oldMessage, $newMessage] = explode(' use ', $message);

            $deprecation = $this->tryToCreateClassMethodDeprecation($oldMessage, $newMessage);
            if ($deprecation) {
                return $deprecation;
            }
        }

        throw new NotImplementedException(sprintf(
            '%s() did not resolve %s messsage, so %s was not created. Implement it.',
            __METHOD__,
            $message,
            DeprecationInterface::class
        ));
    }
}
