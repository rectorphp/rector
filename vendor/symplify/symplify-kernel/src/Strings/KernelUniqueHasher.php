<?php

declare (strict_types=1);
namespace RectorPrefix20210827\Symplify\SymplifyKernel\Strings;

use RectorPrefix20210827\Nette\Utils\Strings;
use RectorPrefix20210827\Symplify\SymplifyKernel\Exception\HttpKernel\TooGenericKernelClassException;
use RectorPrefix20210827\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel;
final class KernelUniqueHasher
{
    /**
     * @var \Symplify\SymplifyKernel\Strings\StringsConverter
     */
    private $stringsConverter;
    public function __construct()
    {
        $this->stringsConverter = new \RectorPrefix20210827\Symplify\SymplifyKernel\Strings\StringsConverter();
    }
    public function hashKernelClass(string $kernelClass) : string
    {
        $this->ensureIsNotGenericKernelClass($kernelClass);
        $shortClassName = (string) \RectorPrefix20210827\Nette\Utils\Strings::after($kernelClass, '\\', -1);
        $userSpecificShortClassName = $shortClassName . \get_current_user();
        return $this->stringsConverter->camelCaseToGlue($userSpecificShortClassName, '_');
    }
    private function ensureIsNotGenericKernelClass(string $kernelClass) : void
    {
        if ($kernelClass !== \RectorPrefix20210827\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel::class) {
            return;
        }
        $message = \sprintf('Instead of "%s", provide final Kernel class', \RectorPrefix20210827\Symplify\SymplifyKernel\HttpKernel\AbstractSymplifyKernel::class);
        throw new \RectorPrefix20210827\Symplify\SymplifyKernel\Exception\HttpKernel\TooGenericKernelClassException($message);
    }
}
