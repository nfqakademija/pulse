<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191202125025 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE answer ADD answer_option_id INT DEFAULT NULL, CHANGE question_id question_id INT DEFAULT NULL, CHANGE survey_id survey_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A259A3BC2B9 FOREIGN KEY (answer_option_id) REFERENCES `option` (id)');
        $this->addSql('CREATE INDEX IDX_DADD4A259A3BC2B9 ON answer (answer_option_id)');
        $this->addSql('ALTER TABLE question CHANGE poll_id poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE responder CHANGE team_lead_id team_lead_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE survey CHANGE poll_id poll_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A259A3BC2B9');
        $this->addSql('DROP INDEX IDX_DADD4A259A3BC2B9 ON answer');
        $this->addSql('ALTER TABLE answer DROP answer_option_id, CHANGE question_id question_id INT DEFAULT NULL, CHANGE survey_id survey_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question CHANGE poll_id poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE responder CHANGE team_lead_id team_lead_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE survey CHANGE poll_id poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
