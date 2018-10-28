<?php declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181028130832 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'CREATE TABLE teams (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, strip VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE leagues (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE leagues_teams (league_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_DB2AE55A58AFC4DE (league_id), INDEX IDX_DB2AE55A296CD8AE (team_id), PRIMARY KEY(league_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE leagues_teams ADD CONSTRAINT FK_DB2AE55A58AFC4DE FOREIGN KEY (league_id) REFERENCES leagues (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE leagues_teams ADD CONSTRAINT FK_DB2AE55A296CD8AE FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE leagues_teams DROP FOREIGN KEY FK_DB2AE55A296CD8AE');
        $this->addSql('ALTER TABLE leagues_teams DROP FOREIGN KEY FK_DB2AE55A58AFC4DE');
        $this->addSql('DROP TABLE teams');
        $this->addSql('DROP TABLE leagues');
        $this->addSql('DROP TABLE leagues_teams');
    }
}
