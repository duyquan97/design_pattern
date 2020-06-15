<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190415105542 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE booking_product_rates_audit (id INT NOT NULL, rev INT NOT NULL, booking_product_id INT DEFAULT NULL, amount DOUBLE PRECISION DEFAULT NULL, currency VARCHAR(3) DEFAULT NULL, date DATETIME DEFAULT NULL, created_at DATETIME DEFAULT NULL, revtype VARCHAR(4) NOT NULL, INDEX rev_17c852a46d93e283fae5ad67fdca0855_idx (rev), PRIMARY KEY(id, rev)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE availability_audit (id INT NOT NULL, rev INT NOT NULL, product_id INT DEFAULT NULL, partner_id INT DEFAULT NULL, date DATE DEFAULT NULL, stock INT DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, revtype VARCHAR(4) NOT NULL, INDEX rev_218b177f5ec5cdeba78c341baeca104f_idx (rev), PRIMARY KEY(id, rev)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE availability ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE channel_managers CHANGE push_bookings push_bookings TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE product_rate ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE booking_product_rates_audit');
        $this->addSql('DROP TABLE availability_audit');
        $this->addSql('ALTER TABLE availability DROP updated_at, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE channel_managers CHANGE push_bookings push_bookings TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_rate DROP updated_at, CHANGE created_at created_at DATETIME NOT NULL');
    }
}
