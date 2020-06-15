<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190416134031 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE channel_managers CHANGE push_bookings push_bookings TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE guests CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE surname surname VARCHAR(255) DEFAULT NULL, CHANGE country_code country_code VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE channel_managers CHANGE push_bookings push_bookings TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE guests CHANGE name name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE surname surname VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, CHANGE country_code country_code VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
