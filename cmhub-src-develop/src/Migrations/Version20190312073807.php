<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190312073807 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );
        $this->addSql("ALTER TABLE channel_managers CHANGE `standard` `push_bookings` TINYINT(1)");
        $this->addSql("ALTER TABLE channel_managers_audit CHANGE `standard` `push_bookings` TINYINT(1)");
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );
        $this->addSql("ALTER TABLE channel_managers CHANGE `push_bookings` `standard` TINYINT(1)");
        $this->addSql("ALTER TABLE channel_managers_audit CHANGE `push_bookings` `standard` TINYINT(1)");
    }
}
