<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\ErrorHandler\ErrorEnhancer;

use RectorPrefix20211020\Symfony\Component\ErrorHandler\Error\FatalError;
use RectorPrefix20211020\Symfony\Component\ErrorHandler\Error\UndefinedMethodError;
/**
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class UndefinedMethodErrorEnhancer implements \RectorPrefix20211020\Symfony\Component\ErrorHandler\ErrorEnhancer\ErrorEnhancerInterface
{
    /**
     * {@inheritdoc}
     * @param \Throwable $error
     */
    public function enhance($error) : ?\Throwable
    {
        if ($error instanceof \RectorPrefix20211020\Symfony\Component\ErrorHandler\Error\FatalError) {
            return null;
        }
        $message = $error->getMessage();
        \preg_match('/^Call to undefined method (.*)::(.*)\\(\\)$/', $message, $matches);
        if (!$matches) {
            return null;
        }
        $className = $matches[1];
        $methodName = $matches[2];
        $message = \sprintf('Attempted to call an undefined method named "%s" of class "%s".', $methodName, $className);
        if ('' === $methodName || !\class_exists($className) || null === ($methods = \get_class_methods($className))) {
            // failed to get the class or its methods on which an unknown method was called (for example on an anonymous class)
            return new \RectorPrefix20211020\Symfony\Component\ErrorHandler\Error\UndefinedMethodError($message, $error);
        }
        $candidates = [];
        foreach ($methods as $definedMethodName) {
            $lev = \levenshtein($methodName, $definedMethodName);
            if ($lev <= \strlen($methodName) / 3 || \false !== \strpos($definedMethodName, $methodName)) {
                $candidates[] = $definedMethodName;
            }
        }
        if ($candidates) {
            \sort($candidates);
            $last = \array_pop($candidates) . '"?';
            if ($candidates) {
                $candidates = 'e.g. "' . \implode('", "', $candidates) . '" or "' . $last;
            } else {
                $candidates = '"' . $last;
            }
            $message .= "\nDid you mean to call " . $candidates;
        }
        return new \RectorPrefix20211020\Symfony\Component\ErrorHandler\Error\UndefinedMethodError($message, $error);
    }
}
