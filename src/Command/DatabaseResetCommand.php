<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'app:db:reset',
    description: 'Reset database: drop all tables, run migrations and load fixtures'
)]
class DatabaseResetCommand extends Command
{
    public function __construct(
        private Connection $connection
    ) {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this
            ->setDescription('Completely reset the database and reload with fresh data')
            ->setHelp('This command will:
1. Drop all database tables (keeps database file)
2. Run all migrations from scratch to recreate schema
3. Load fixture data

WARNING: This will destroy all existing data in the tables!')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Force the operation without confirmation'
            )
            ->addOption(
                'no-fixtures',
                null,
                InputOption::VALUE_NONE,
                'Skip loading fixtures after migration'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🗃️  Database Reset Tool');
        
        if (!$input->getOption('force')) {
            $io->warning([
                'This will completely reset your database!',
                'All existing data will be lost permanently.',
            ]);

            if (!$io->confirm('Are you sure you want to continue?', false)) {
                $io->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $io->section('Step 1: Dropping all tables with proper transaction handling');
        
        try {
            // Start transaction and disable foreign key constraints
            $this->connection->beginTransaction();
            $io->text('Starting transaction and disabling foreign key constraints...');
            
            $this->connection->executeStatement('PRAGMA foreign_keys = OFF');
            
            // Get all table names
            $tables = $this->connection->createSchemaManager()->listTableNames();
            $io->text(sprintf('Found %d tables to drop', count($tables)));
            
            // Drop all tables
            foreach ($tables as $table) {
                $io->text("Dropping table: {$table}");
                $this->connection->executeStatement("DROP TABLE IF EXISTS `{$table}`");
            }
            
            // Re-enable foreign key constraints
            $this->connection->executeStatement('PRAGMA foreign_keys = ON');
            
            // Commit transaction
            $this->connection->commit();
            $io->success('All tables dropped successfully in transaction');
            
        } catch (\Exception $e) {
            $this->connection->rollBack();
            $io->error('Failed to drop tables: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->section('Step 2: Running migrations to recreate schema');
        if (!$this->runCommand(['php', 'bin/console', 'doctrine:migrations:migrate', '--no-interaction'], $io)) {
            return Command::FAILURE;
        }
        $io->success('Database schema recreated via migrations');

        if (!$input->getOption('no-fixtures')) {
            $io->section('Step 3: Loading fixtures');
            if (!$this->runCommand(['php', 'bin/console', 'doctrine:fixtures:load', '--no-interaction'], $io)) {
                $io->warning('Failed to load fixtures, but database reset was successful');
                return Command::SUCCESS;
            }
            $io->success('Fixtures loaded successfully');
        } else {
            $io->note('Skipping fixtures loading as requested');
        }

        $io->newLine();
        $io->success('🎉 Database reset completed successfully!');
        
        if (!$input->getOption('no-fixtures')) {
            $io->table(['Test Accounts'], [
                ['Email: test@example.com | Password: password123'],
                ['Email: anna.kowalska@example.com | Password: password456'],
                ['Email: jan.nowak@example.com | Password: password789'],
                ['Each user has multiple budgets with sample transactions']
            ]);
        }

        return Command::SUCCESS;
    }

    private function runCommand(array $command, SymfonyStyle $io): bool
    {
        $process = new Process($command);
        $process->setTimeout(300); // 5 minutes timeout

        try {
            $io->text('Running: ' . implode(' ', $command));
            
            $process->mustRun(function ($type, $buffer) use ($io) {
                if (Process::ERR === $type) {
                    $io->text('<fg=red>' . $buffer . '</>');
                } else {
                    $io->text('<fg=gray>' . $buffer . '</>');
                }
            });

            return true;
        } catch (ProcessFailedException $exception) {
            $io->error([
                'Command failed: ' . implode(' ', $command),
                $exception->getMessage()
            ]);
            return false;
        }
    }
}