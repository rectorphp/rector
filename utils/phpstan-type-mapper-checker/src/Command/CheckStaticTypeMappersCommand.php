<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanTypeMapperChecker\Command;

use Rector\Core\Console\Command\AbstractCommand;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\Utils\PHPStanTypeMapperChecker\Validator\MissingPHPStanTypeMappersResolver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;

final class CheckStaticTypeMappersCommand extends AbstractCommand
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MissingPHPStanTypeMappersResolver
     */
    private $missingPHPStanTypeMappersResolver;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        MissingPHPStanTypeMappersResolver $missingPHPStanTypeMappersResolver
    ) {
        parent::__construct();

        $this->symfonyStyle = $symfonyStyle;
        $this->missingPHPStanTypeMappersResolver = $missingPHPStanTypeMappersResolver;
    }

    protected function configure(): void
    {
        $this->setDescription('[DEV] Check PHPStan types to TypeMappers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $missingTypeNodeClasses = $this->missingPHPStanTypeMappersResolver->resolve();

        if ($missingTypeNodeClasses === []) {
            $this->symfonyStyle->success('All PHPStan Types and PHPStan Doc Types are covered');
            return ShellCode::SUCCESS;
        }

        foreach ($missingTypeNodeClasses as $missingTypeNodeClass) {
            $errorMessage = sprintf(
                'Add new class to "%s" that implements "%s" for "%s" type',
                'packages/phpstan-static-type-mapper/src/TypeMapper',
                TypeMapperInterface::class,
                $missingTypeNodeClass
            );
            $this->symfonyStyle->error($errorMessage);
        }

        return ShellCode::ERROR;
    }
}
