<?php

namespace App\Command;

use App\Entity\Expense;
use App\Service\ExpenseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-expense-occurrences-amounts',
    description: 'Update amounts in existing expense occurrences that have zero/null amounts',
)]
class CreateExpenseOccurrencesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExpenseService $expenseService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be done without making changes')
            ->setHelp('This command updates amounts in existing expense occurrences that have zero or null amounts by copying from their parent expense.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');

        if ($dryRun) {
            $io->warning('DRY RUN MODE - No changes will be made');
        }

        $io->title('Updating Amounts in Existing Expense Occurrences');

        // Find all occurrences with zero or null amounts
        $occurrencesWithZeroAmount = $this->entityManager->getRepository(\App\Entity\ExpenseOccurrence::class)
            ->createQueryBuilder('o')
            ->where('o.amount IS NULL OR o.amount = 0')
            ->getQuery()
            ->getResult();

        $io->info(sprintf('Found %d expense occurrences with zero/null amounts', count($occurrencesWithZeroAmount)));

        $updatedCount = 0;

        foreach ($occurrencesWithZeroAmount as $occurrence) {
            $expense = $occurrence->getExpense();
            $oldAmount = $occurrence->getAmount();
            $newAmount = $expense->getAmount();

            $io->text(sprintf(
                'Updating occurrence ID %d for expense "%s": %s → %s',
                $occurrence->getId(),
                $expense->getName(),
                $oldAmount ?? 'NULL',
                $newAmount
            ));

            if (!$dryRun) {
                $occurrence->setAmount($newAmount);
                $updatedCount++;
            } else {
                $updatedCount++;
            }
        }

        if (!$dryRun) {
            $this->entityManager->flush();
            $io->success(sprintf('Successfully updated amounts in %d expense occurrences', $updatedCount));
        } else {
            $io->success(sprintf('Would update amounts in %d expense occurrences (dry run)', $updatedCount));
        }

        return Command::SUCCESS;
    }
}
