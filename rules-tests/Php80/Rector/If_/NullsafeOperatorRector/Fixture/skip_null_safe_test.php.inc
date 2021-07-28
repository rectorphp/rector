<?php declare(strict_types = 1);

namespace App\Module\Accounting\Policy\AccountingOrganization;

use App\Module\Accounting\Domain\AccountingOrganization\Event\AccountingOrganizationRegistered;
use App\Module\ContactAdministration\Domain\ContactCard\Command\AddContactCardTypeCommand;
use App\Module\ContactAdministration\Domain\ContactCard\ValueObject\ContactCardId;
use App\Module\ContactAdministration\Domain\ContactCard\ValueObject\ContactCardType;
use App\Module\Core\Domain\CommandInterface;
use Generator;

final class AddContactCardTypesWhenAccountingOrganizationRegisteredPolicy
{
    /**
     * @return Generator<CommandInterface[]>
     */
    public function __invoke(AccountingOrganizationRegistered $event): Generator
    {
        /** @var CommandInterface[] $commands */
        $commands = [];

        if ($event->parameters->accountantId !== null) {
            $commands[] = new AddContactCardTypeCommand(
                ContactCardId::fromIdentity($event->parameters->accountantId),
                new ContactCardType(ContactCardType::ACCOUNTANT)
            );
        }

        $commands[] = new AddContactCardTypeCommand(
            ContactCardId::fromIdentity($event->subjectId),
            new ContactCardType(ContactCardType::ACCOUNTING_ORGANIZATION)
        );

        yield $commands;
    }
}
