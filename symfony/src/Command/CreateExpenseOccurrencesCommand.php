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
    name: 'app:create-expense-occurrences',
    description: 'Create expense occurrences for existing recurring expenses that don\'t have them',
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
            ->addOption('months-back', null, InputOption::VALUE_OPTIONAL, 'Number of months back to create occurrences for', 3)
            ->setHelp('This command creates expense occurrences for existing recurring expenses that don\'t have occurrences yet.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = $input->getOption('dry-run');
        $monthsBack = (int) $input->getOption('months-back');

        if ($dryRun) {
            $io->warning('DRY RUN MODE - No changes will be made');
        }

        $io->title('Creating Expense Occurrences for Existing Recurring Expenses');

        // Find all recurring expenses
        $recurringExpenses = $this->entityManager->getRepository(Expense::class)
            ->createQueryBuilder('e')
            ->where('e.recurringFrequency > 0')
            ->getQuery()
            ->getResult();

        $io->info(sprintf('Found %d recurring expenses', count($recurringExpenses)));

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($recurringExpenses as $expense) {
            $io->section(sprintf('Processing expense: %s (ID: %d)', $expense->getName(), $expense->getId()));

            // Calculate date range for occurrences
            $endDate = new \DateTime();
            $startDate = (clone $endDate)->modify("-{$monthsBack} months");
            $startDate->setDate($startDate->format('Y'), $startDate->format('m'), 1); // First day of month

            $io->text(sprintf('Creating occurrences from %s to %s', $startDate->format('Y-m-d'), $endDate->format('Y-m-d')));

            $occurrencesCreated = 0;

            // Create occurrences for each month in the range
            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                // Check if occurrence already exists for this expense and date
                $existingOccurrence = $this->entityManager->getRepository(\App\Entity\ExpenseOccurrence::class)
                    ->findOneBy([
                        'expense' => $expense,
                        'occurrenceDate' => $currentDate,
                    ]);

                if ($existingOccurrence) {
                    $io->text(sprintf('  - Occurrence already exists for %s', $currentDate->format('Y-m')));
                    $skippedCount++;
                } else {
                    if (!$dryRun) {
                        $occurrence = $this->expenseService->createExpenseOccurrence($expense, $currentDate);
                        $this->entityManager->persist($occurrence);
                        $io->text(sprintf('  + Created occurrence for %s with amount %s', $currentDate->format('Y-m'), $occurrence->getAmount()));
                        $occurrencesCreated++;
                        $createdCount++;
                    } else {
                        $io->text(sprintf('  + Would create occurrence for %s with amount %s', $currentDate->format('Y-m'), $expense->getAmount()));
                        $occurrencesCreated++;
                        $createdCount++;
                    }
                }

                // Move to next occurrence based on frequency
                $currentDate->modify("+{$expense->getRecurringFrequency()} month");
            }

            if ($occurrencesCreated > 0) {
                $io->success(sprintf('Created %d occurrences for expense "%s"', $occurrencesCreated, $expense->getName()));
            } else {
                $io->note('No new occurrences needed for this expense');
            }
        }

        if (!$dryRun) {
            $this->entityManager->flush();
            $io->success(sprintf('Successfully created %d expense occurrences', $createdCount));
        } else {
            $io->success(sprintf('Would create %d expense occurrences (dry run)', $createdCount));
        }

        if ($skippedCount > 0) {
            $io->note(sprintf('%d occurrences were skipped (already existed)', $skippedCount));
        }

        return Command::SUCCESS;
    }
}
