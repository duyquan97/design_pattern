<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190510083427 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE broadcasts (id INT AUTO_INCREMENT NOT NULL, transaction_id VARCHAR(255) DEFAULT NULL, request LONGTEXT NOT NULL, response LONGTEXT DEFAULT NULL, status_code VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, status VARCHAR(255) NOT NULL, channel VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, INDEX broadcast (transaction_id, status, type), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE availability ADD transaction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF2FC0CB0F FOREIGN KEY (transaction_id) REFERENCES broadcasts (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_3FB7A2BF2FC0CB0F ON availability (transaction_id)');
        $this->addSql('ALTER TABLE availability_audit ADD transaction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_rate ADD transaction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_rate ADD CONSTRAINT FK_52041D982FC0CB0F FOREIGN KEY (transaction_id) REFERENCES broadcasts (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_52041D982FC0CB0F ON product_rate (transaction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF2FC0CB0F');
        $this->addSql('ALTER TABLE product_rate DROP FOREIGN KEY FK_52041D982FC0CB0F');
        $this->addSql('DROP TABLE broadcasts');
        $this->addSql('DROP INDEX IDX_3FB7A2BF2FC0CB0F ON availability');
        $this->addSql('ALTER TABLE availability DROP transaction_id');
        $this->addSql('ALTER TABLE availability_audit DROP transaction_id');
        $this->addSql('DROP INDEX IDX_52041D982FC0CB0F ON product_rate');
        $this->addSql('ALTER TABLE product_rate DROP transaction_id');
    }
}
