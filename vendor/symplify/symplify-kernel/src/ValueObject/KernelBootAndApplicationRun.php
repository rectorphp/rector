<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SymplifyKernel\ValueObject;

use RectorPrefix20210510\Symfony\Component\Console\Application;
use RectorPrefix20210510\Symfony\Component\HttpKernel\KernelInterface;
use RectorPrefix20210510\Symplify\PackageBuilder\Console\Input\StaticInputDetector;
use RectorPrefix20210510\Symplify\PackageBuilder\Console\ShellCode;
use RectorPrefix20210510\Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;
use RectorPrefix20210510\Symplify\PackageBuilder\Contract\HttpKernel\ExtraConfigAwareKernelInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20210510\Symplify\SymplifyKernel\Exception\BootException;
use Throwable;
final class KernelBootAndApplicationRun
{
    /**
     * @var class-string
     */
    private $kernelClass;
    /**
     * @var string[]|SmartFileInfo[]
     */
    private $extraConfigs = [];
    /**
     * @param class-string $kernelClass
     * @param string[]|SmartFileInfo[] $extraConfigs
     */
    public function __construct(string $kernelClass, array $extraConfigs = [])
    {
        $this->setKernelClass($kernelClass);
        $this->extraConfigs = $extraConfigs;
    }
    public function run() : void
    {
        try {
            $this->booKernelAndRunApplication();
        } catch (Throwable $throwable) {
            $symfonyStyleFactory = new SymfonyStyleFactory();
            $symfonyStyle = $symfonyStyleFactory->create();
            $symfonyStyle->error($throwable->getMessage());
            exit(ShellCode::ERROR);
        }
    }
    private function createKernel() : KernelInterface
    {
        // random has is needed, so cache is invalidated and changes from config are loaded
        $environment = 'prod' . \random_int(1, 100000);
        $kernelClass = $this->kernelClass;
        $kernel = new $kernelClass($environment, StaticInputDetector::isDebug());
        $this->setExtraConfigs($kernel, $kernelClass);
        return $kernel;
    }
    private function booKernelAndRunApplication() : void
    {
        $kernel = $this->createKernel();
        if ($kernel instanceof ExtraConfigAwareKernelInterface && $this->extraConfigs !== []) {
            $kernel->setConfigs($this->extraConfigs);
        }
        $kernel->boot();
        $container = $kernel->getContainer();
        /** @var Application $application */
        $application = $container->get(Application::class);
        exit($application->run());
    }
    private function setExtraConfigs(KernelInterface $kernel, string $kernelClass) : void
    {
        if ($this->extraConfigs === []) {
            return;
        }
        if (\is_a($kernel, ExtraConfigAwareKernelInterface::class, \true)) {
            /** @var ExtraConfigAwareKernelInterface $kernel */
            $kernel->setConfigs($this->extraConfigs);
        } else {
            $message = \sprintf('Extra configs are set, but the "%s" kernel class is missing "%s" interface', $kernelClass, ExtraConfigAwareKernelInterface::class);
            throw new BootException($message);
        }
    }
    /**
     * @param class-string $kernelClass
     */
    private function setKernelClass(string $kernelClass) : void
    {
        if (!\is_a($kernelClass, KernelInterface::class, \true)) {
            $message = \sprintf('Class "%s" must by type of "%s"', $kernelClass, KernelInterface::class);
            throw new BootException($message);
        }
        $this->kernelClass = $kernelClass;
    }
}
