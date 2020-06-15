<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181022141606 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE fos_user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_583D1F3E5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, date_of_birth DATETIME DEFAULT NULL, firstname VARCHAR(64) DEFAULT NULL, lastname VARCHAR(64) DEFAULT NULL, website VARCHAR(64) DEFAULT NULL, biography VARCHAR(1000) DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, locale VARCHAR(8) DEFAULT NULL, timezone VARCHAR(64) DEFAULT NULL, phone VARCHAR(64) DEFAULT NULL, facebook_uid VARCHAR(255) DEFAULT NULL, facebook_name VARCHAR(255) DEFAULT NULL, facebook_data JSON DEFAULT NULL, twitter_uid VARCHAR(255) DEFAULT NULL, twitter_name VARCHAR(255) DEFAULT NULL, twitter_data JSON DEFAULT NULL, gplus_uid VARCHAR(255) DEFAULT NULL, gplus_name VARCHAR(255) DEFAULT NULL, gplus_data JSON DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, two_step_code VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_C560D76192FC23A8 (username_canonical), UNIQUE INDEX UNIQ_C560D761A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_C560D761C05FB297 (confirmation_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fos_user_user_group (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_B3C77447A76ED395 (user_id), INDEX IDX_B3C77447FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guests (id INT AUTO_INCREMENT NOT NULL, booking_product_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, surname VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(255) DEFAULT NULL, state VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, country_code VARCHAR(2) DEFAULT NULL, age INT DEFAULT NULL, is_main TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_4D11BCB2DC1E052B (booking_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, partner_id INT DEFAULT NULL, identifier VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, sellable TINYINT(1) NOT NULL, reservable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D34A04AD9393F8FE (partner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_rate (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, partner_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, date DATE NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_52041D984584665A (product_id), INDEX IDX_52041D989393F8FE (partner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE channel_managers (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, identifier VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, standard TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_DEF993AFA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_product_rates (id INT AUTO_INCREMENT NOT NULL, booking_product_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, currency VARCHAR(3) NOT NULL, date DATETIME NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_EEB190A0DC1E052B (booking_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE availability (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, partner_id INT DEFAULT NULL, date DATE NOT NULL, stock INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_3FB7A2BF4584665A (product_id), INDEX IDX_3FB7A2BF9393F8FE (partner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner (id INT AUTO_INCREMENT NOT NULL, channel_manager_id INT DEFAULT NULL, user_id INT DEFAULT NULL, identifier VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_312B3E16CEF764F5 (channel_manager_id), INDEX IDX_312B3E16A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE booking_products (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, booking_id INT DEFAULT NULL, total_amount DOUBLE PRECISION NOT NULL, currency VARCHAR(3) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_888EC4D74584665A (product_id), INDEX IDX_888EC4D73301C60 (booking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bookings (id INT AUTO_INCREMENT NOT NULL, partner_id INT DEFAULT NULL, identifier VARCHAR(255) NOT NULL, create_date DATETIME NOT NULL, last_modify_date DATETIME NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, total_amount DOUBLE PRECISION NOT NULL, currency VARCHAR(3) NOT NULL, status VARCHAR(255) NOT NULL, requests LONGTEXT DEFAULT NULL, comments LONGTEXT DEFAULT NULL, INDEX IDX_7A853C359393F8FE (partner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cm_users (id INT AUTO_INCREMENT NOT NULL, channel_manager_id INT DEFAULT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, INDEX IDX_FE5EBDC2CEF764F5 (channel_manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447A76ED395 FOREIGN KEY (user_id) REFERENCES fos_user_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fos_user_user_group ADD CONSTRAINT FK_B3C77447FE54D947 FOREIGN KEY (group_id) REFERENCES fos_user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE guests ADD CONSTRAINT FK_4D11BCB2DC1E052B FOREIGN KEY (booking_product_id) REFERENCES booking_products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD9393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_rate ADD CONSTRAINT FK_52041D984584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_rate ADD CONSTRAINT FK_52041D989393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id)');
        $this->addSql('ALTER TABLE channel_managers ADD CONSTRAINT FK_DEF993AFA76ED395 FOREIGN KEY (user_id) REFERENCES cm_users (id)');
        $this->addSql('ALTER TABLE booking_product_rates ADD CONSTRAINT FK_EEB190A0DC1E052B FOREIGN KEY (booking_product_id) REFERENCES booking_products (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE availability ADD CONSTRAINT FK_3FB7A2BF9393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id)');
        $this->addSql('ALTER TABLE partner ADD CONSTRAINT FK_312B3E16CEF764F5 FOREIGN KEY (channel_manager_id) REFERENCES channel_managers (id)');
        $this->addSql('ALTER TABLE partner ADD CONSTRAINT FK_312B3E16A76ED395 FOREIGN KEY (user_id) REFERENCES cm_users (id)');
        $this->addSql('ALTER TABLE booking_products ADD CONSTRAINT FK_888EC4D74584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE booking_products ADD CONSTRAINT FK_888EC4D73301C60 FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bookings ADD CONSTRAINT FK_7A853C359393F8FE FOREIGN KEY (partner_id) REFERENCES partner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cm_users ADD CONSTRAINT FK_FE5EBDC2CEF764F5 FOREIGN KEY (channel_manager_id) REFERENCES channel_managers (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447FE54D947');
        $this->addSql('ALTER TABLE fos_user_user_group DROP FOREIGN KEY FK_B3C77447A76ED395');
        $this->addSql('ALTER TABLE product_rate DROP FOREIGN KEY FK_52041D984584665A');
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF4584665A');
        $this->addSql('ALTER TABLE booking_products DROP FOREIGN KEY FK_888EC4D74584665A');
        $this->addSql('ALTER TABLE partner DROP FOREIGN KEY FK_312B3E16CEF764F5');
        $this->addSql('ALTER TABLE cm_users DROP FOREIGN KEY FK_FE5EBDC2CEF764F5');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD9393F8FE');
        $this->addSql('ALTER TABLE product_rate DROP FOREIGN KEY FK_52041D989393F8FE');
        $this->addSql('ALTER TABLE availability DROP FOREIGN KEY FK_3FB7A2BF9393F8FE');
        $this->addSql('ALTER TABLE bookings DROP FOREIGN KEY FK_7A853C359393F8FE');
        $this->addSql('ALTER TABLE guests DROP FOREIGN KEY FK_4D11BCB2DC1E052B');
        $this->addSql('ALTER TABLE booking_product_rates DROP FOREIGN KEY FK_EEB190A0DC1E052B');
        $this->addSql('ALTER TABLE booking_products DROP FOREIGN KEY FK_888EC4D73301C60');
        $this->addSql('ALTER TABLE channel_managers DROP FOREIGN KEY FK_DEF993AFA76ED395');
        $this->addSql('ALTER TABLE partner DROP FOREIGN KEY FK_312B3E16A76ED395');
        $this->addSql('DROP TABLE fos_user_group');
        $this->addSql('DROP TABLE fos_user_user');
        $this->addSql('DROP TABLE fos_user_user_group');
        $this->addSql('DROP TABLE guests');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_rate');
        $this->addSql('DROP TABLE channel_managers');
        $this->addSql('DROP TABLE booking_product_rates');
        $this->addSql('DROP TABLE availability');
        $this->addSql('DROP TABLE partner');
        $this->addSql('DROP TABLE booking_products');
        $this->addSql('DROP TABLE bookings');
        $this->addSql('DROP TABLE cm_users');
    }
}
