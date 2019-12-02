<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191202110335 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE question CHANGE poll_id poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE answer CHANGE question_id question_id INT DEFAULT NULL, CHANGE survey_id survey_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE responder CHANGE team_lead_id team_lead_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE survey ADD name VARCHAR(255) NOT NULL, CHANGE poll_id poll_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE answer CHANGE question_id question_id INT DEFAULT NULL, CHANGE survey_id survey_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question CHANGE poll_id poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE responder CHANGE team_lead_id team_lead_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE survey DROP name, CHANGE poll_id poll_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
