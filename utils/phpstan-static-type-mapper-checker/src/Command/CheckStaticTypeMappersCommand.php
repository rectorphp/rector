<?php

declare(strict_types=1);

namespace Rector\Utils\PHPStanStaticTypeMapperChecker\Command;

use PHPStan\Type\NonexistentParentClassType;
use PHPStan\Type\ParserNodeTypeToPHPStanType;
use Rector\Core\Console\Command\AbstractCommand;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\Utils\PHPStanStaticTypeMapperChecker\Finder\PHPStanTypeClassFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\ShellCode;

final class CheckStaticTypeMappersCommand extends AbstractCommand
{
    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers = [];

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var PHPStanTypeClassFinder
     */
    private $phpStanTypeClassFinder;

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(
        array $typeMappers,
        SymfonyStyle $symfonyStyle,
        PHPStanTypeClassFinder $phpStanTypeClassFinder
    ) {
        $this->typeMappers = $typeMappers;
        $this->symfonyStyle = $symfonyStyle;
        $this->phpStanTypeClassFinder = $phpStanTypeClassFinder;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('[DEV] check PHPStan types to TypeMappers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $missingNodeClasses = $this->getMissingNodeClasses();
        if ($missingNodeClasses === []) {
            $this->symfonyStyle->success('All PHPStan Types are covered by TypeMapper');

            return ShellCode::SUCCESS;
        }

        foreach ($missingNodeClasses as $missingNodeClass) {
            $errorMessage = sprintf(
                'Add new class to "%s" that implements "%s" for "%s" type',
                'packages/phpstan-static-type-mapper/src/TypeMapper',
                TypeMapperInterface::class,
                $missingNodeClass
            );
            $this->symfonyStyle->error($errorMessage);
        }

        return ShellCode::ERROR;
    }

    /**
     * @return class-string[]
     */
    private function getMissingNodeClasses(): array
    {
        $phpStanTypeClasses = $this->phpStanTypeClassFinder->find();
        $supportedTypeClasses = $this->getSupportedTypeClasses();

        $unsupportedTypeClasses = [];
        foreach ($phpStanTypeClasses as $phpStanTypeClass) {
            foreach ($supportedTypeClasses as $supportedPHPStanTypeClass) {
                if (is_a($phpStanTypeClass, $supportedPHPStanTypeClass, true)) {
                    continue 2;
                }
            }

            $unsupportedTypeClasses[] = $phpStanTypeClass;
        }

        $typesToRemove = [NonexistentParentClassType::class, ParserNodeTypeToPHPStanType::class];

        return array_diff($unsupportedTypeClasses, $typesToRemove);
    }

    /**
     * @return string[]
     */
    private function getSupportedTypeClasses(): array
    {
        $supportedPHPStanTypeClasses = [];
        foreach ($this->typeMappers as $typeMappers) {
            $supportedPHPStanTypeClasses[] = $typeMappers->getNodeClass();
        }

        return $supportedPHPStanTypeClasses;
    }
}
