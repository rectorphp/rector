<?php declare(strict_types=1);

namespace Rector\Bridge\Symfony\DependencyInjection;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\BetterReflection\BetterReflection;
use Rector\BetterReflection\Util\Autoload\ClassLoader;
use Rector\BetterReflection\Util\Autoload\ClassLoaderMethod\FileCacheLoader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symplify\PackageBuilder\DependencyInjection\CompilerPass\PublicForTestsCompilerPass;

final class ContainerFactory
{
    /**
     * @var Container[]
     */
    private $containersByKernelClass = [];

    public function createFromKernelClass(string $kernelClass): Container
    {
        if (isset($this->containersByKernelClass[$kernelClass])) {
            return $this->containersByKernelClass[$kernelClass];
        }

        return $this->containersByKernelClass[$kernelClass] = $this->createContainerFromKernelClass($kernelClass);
    }

    private function createContainerFromKernelClass(string $kernelClass): Container
    {
        // add https://github.com/symplify/packagebuilder#9-make-services-public-for-tests-only somehow

        $classInfo = (new \Rector\BetterReflection\BetterReflection())->classReflector()->reflect($kernelClass);

        $methodNode = $classInfo->getMethod('build');

        /** @var ClassMethod $classMethod */
        $classMethod = $methodNode->getAst();

        $compilerClass = 'Symplify\PackageBuilder\DependencyInjection\CompilerPass\PublicForTestsCompilerPass';

        $methodCall = new MethodCall($classMethod->params[0]->var, 'addCompilerPass');
        $methodCall->args[] = new Arg(new New_(new Identifier($compilerClass)));

        $bodyAst = $methodNode->getBodyAst();
        $bodyAst[] = new Expression($methodCall);

        $methodNode->setBodyFromAst($bodyAst);

        $loader = new ClassLoader(FileCacheLoader::defaultFileCacheLoader(__DIR__));

        // Call this any time before instantiating the class
        $loader->addClass($classInfo);

        $kernel = $this->createKernelFromKernelClass($kernelClass);
        $kernel->boot();

        return $kernel->getContainer();
    }

    private function createKernelFromKernelClass(string $kernelClass): Kernel
    {
        $environment = $options['environment'] ?? $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = (bool) ($options['debug'] ?? $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true);

        return new $kernelClass($environment, $debug);
    }
}
