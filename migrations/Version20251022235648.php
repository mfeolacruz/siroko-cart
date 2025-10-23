<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250124000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create carts table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE carts (
            id VARCHAR(36) NOT NULL COMMENT \'(DC2Type:cart_id)\',
            user_id VARCHAR(36) DEFAULT NULL COMMENT \'(DC2Type:user_id)\',
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE carts');
    }
}
