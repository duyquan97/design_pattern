<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190117150323 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_audit (id INT NOT NULL, rev INT NOT NULL, partner_id INT DEFAULT NULL, master_product_id INT DEFAULT NULL, identifier VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, sellable TINYINT(1) DEFAULT NULL, reservable TINYINT(1) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, revtype VARCHAR(4) NOT NULL, INDEX rev_e6e41b81419a01db7854bd453c13dc6d_idx (rev), PRIMARY KEY(id, rev)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cm_users_audit (id INT NOT NULL, rev INT NOT NULL, channel_manager_id INT DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, revtype VARCHAR(4) NOT NULL, INDEX rev_f8884a33b23745b748734eae30f88d61_idx (rev), PRIMARY KEY(id, rev)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partner_audit (id INT NOT NULL, rev INT NOT NULL, channel_manager_id INT DEFAULT NULL, user_id INT DEFAULT NULL, identifier VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, last_accessed_at DATETIME DEFAULT NULL, revtype VARCHAR(4) NOT NULL, INDEX rev_c69296ef3ee501b7ce98123a9b1f4edf_idx (rev), PRIMARY KEY(id, rev)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE channel_managers_audit (id INT NOT NULL, rev INT NOT NULL, user_id INT DEFAULT NULL, identifier VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, standard TINYINT(1) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP, revtype VARCHAR(4) NOT NULL, INDEX rev_30b4f45033cebccdbbbc7775db6b8339_idx (rev), PRIMARY KEY(id, rev)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE revisions (id INT AUTO_INCREMENT NOT NULL, timestamp DATETIME NOT NULL, username VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_audit');
        $this->addSql('DROP TABLE cm_users_audit');
        $this->addSql('DROP TABLE partner_audit');
        $this->addSql('DROP TABLE channel_managers_audit');
        $this->addSql('DROP TABLE revisions');
    }
}
