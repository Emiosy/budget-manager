<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Complete Budget Manager database schema with UUID support
 * Creates all tables: users (UUID), budgets (UUID), transactions (BIGINT)
 */
final class Version20250101010000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create complete Budget Manager schema with UUID for users/budgets and BIGINT for transactions';
    }

    public function up(Schema $schema): void
    {
        // Create user table with UUID primary key
        $this->addSql('CREATE TABLE user (
            id BLOB NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles CLOB NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_active BOOLEAN DEFAULT 1 NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');

        // Create budget table with UUID primary key and foreign key to user
        $this->addSql('CREATE TABLE budget (
            id BLOB NOT NULL,
            user_id BLOB NOT NULL,
            name VARCHAR(255) NOT NULL,
            description CLOB DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            FOREIGN KEY (user_id) REFERENCES user (id)
        )');
        $this->addSql('CREATE INDEX IDX_73F2F77BA76ED395 ON budget (user_id)');

        // Create budget_transaction table with BIGINT primary key and UUID foreign key to budget
        $this->addSql('CREATE TABLE budget_transaction (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            budget_id BLOB NOT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            type VARCHAR(20) NOT NULL,
            comment VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (budget_id) REFERENCES budget (id)
        )');
        $this->addSql('CREATE INDEX IDX_43D438E36ABA6B8 ON budget_transaction (budget_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE budget_transaction');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE user');
    }
}