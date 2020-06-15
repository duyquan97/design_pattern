<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190117135437 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product ADD master_product_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD4D9AC4D4 FOREIGN KEY (master_product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD4D9AC4D4 ON product (master_product_id)');
        $this->addSql('ALTER TABLE partner CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE channel_managers CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE channel_managers CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE partner CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD4D9AC4D4');
        $this->addSql('DROP INDEX IDX_D34A04AD4D9AC4D4 ON product');
        $this->addSql('ALTER TABLE product DROP master_product_id, CHANGE created_at created_at DATETIME NOT NULL');
    }
}
