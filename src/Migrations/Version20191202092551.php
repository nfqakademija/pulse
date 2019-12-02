<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191202092551 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, poll_id INT DEFAULT NULL, datetime DATETIME NOT NULL, INDEX IDX_AD5F9BFC3C947C0F (poll_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE answer ADD survey_id INT DEFAULT NULL, CHANGE question_id question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('CREATE INDEX IDX_DADD4A25B3FE509D ON answer (survey_id)');
        $this->addSql('ALTER TABLE question CHANGE poll_id poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE responder CHANGE team_lead_id team_lead_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25B3FE509D');
        $this->addSql('DROP TABLE survey');
        $this->addSql('DROP INDEX IDX_DADD4A25B3FE509D ON answer');
        $this->addSql('ALTER TABLE answer DROP survey_id, CHANGE question_id question_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question CHANGE poll_id poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE responder CHANGE team_lead_id team_lead_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
