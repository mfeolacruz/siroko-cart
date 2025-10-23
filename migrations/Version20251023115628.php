<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023115628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cart_items (id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:cart_item_id)\', cart_id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:cart_id)\', product_id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:product_id)\', product_name VARCHAR(255) NOT NULL COMMENT \'(DC2Type:product_name)\', unit_price JSON NOT NULL COMMENT \'(DC2Type:money)\', quantity INT NOT NULL COMMENT \'(DC2Type:quantity)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BEF484451AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE carts (id VARCHAR(255) NOT NULL COMMENT \'(DC2Type:cart_id)\', user_id VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:user_id)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart_items ADD CONSTRAINT FK_BEF484451AD5CDBF FOREIGN KEY (cart_id) REFERENCES carts (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484451AD5CDBF');
        $this->addSql('DROP TABLE cart_items');
        $this->addSql('DROP TABLE carts');
    }
}
