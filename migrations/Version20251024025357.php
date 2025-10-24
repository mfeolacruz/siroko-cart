<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251024025357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE order_items (id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:order_item_id)\', order_id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:order_id)\', product_id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:product_id)\', product_name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:product_name)\', unit_price JSON NOT NULL COMMENT \'(DC2Type:money)\', quantity INT NOT NULL COMMENT \'(DC2Type:quantity)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_62809DB08D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE orders (id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:order_id)\', user_id VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:user_id)\', status VARCHAR(255) NOT NULL COMMENT \'(DC2Type:order_status)\', total JSON NOT NULL COMMENT \'(DC2Type:money)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB08D9F6D38');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
    }
}
