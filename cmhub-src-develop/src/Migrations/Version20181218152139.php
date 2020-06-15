<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181218152139 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE partner DROP FOREIGN KEY FK_312B3E16A76ED395');
        $this->addSql('ALTER TABLE partner ADD CONSTRAINT FK_312B3E16A76ED395 FOREIGN KEY (user_id) REFERENCES cm_users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE channel_managers DROP FOREIGN KEY FK_DEF993AFA76ED395');
        $this->addSql('ALTER TABLE channel_managers ADD CONSTRAINT FK_DEF993AFA76ED395 FOREIGN KEY (user_id) REFERENCES cm_users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE channel_managers DROP FOREIGN KEY FK_DEF993AFA76ED395');
        $this->addSql('ALTER TABLE channel_managers ADD CONSTRAINT FK_DEF993AFA76ED395 FOREIGN KEY (user_id) REFERENCES cm_users (id)');
        $this->addSql('ALTER TABLE partner DROP FOREIGN KEY FK_312B3E16A76ED395');
        $this->addSql('ALTER TABLE partner ADD CONSTRAINT FK_312B3E16A76ED395 FOREIGN KEY (user_id) REFERENCES cm_users (id)');
    }
}
