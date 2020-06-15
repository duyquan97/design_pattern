<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190531145323 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE broadcasts ADD partner_id INT DEFAULT NULL, DROP request, CHANGE response response VARCHAR(255) DEFAULT NULL, CHANGE status_code status_code VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE broadcasts ADD CONSTRAINT FK_D64238E49393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_D64238E49393F8FE ON broadcasts (partner_id)');
        $this->addSql('ALTER TABLE import_data_history ADD type VARCHAR(255) DEFAULT \'Room\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE broadcasts DROP FOREIGN KEY FK_D64238E49393F8FE');
        $this->addSql('DROP INDEX IDX_D64238E49393F8FE ON broadcasts');
        $this->addSql('ALTER TABLE broadcasts ADD request LONGTEXT NOT NULL COLLATE utf8_unicode_ci, DROP partner_id, CHANGE status_code status_code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE response response LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE import_data_history DROP type');
    }
}
