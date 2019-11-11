<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191111184941 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE admin_role (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mood (id INT AUTO_INCREMENT NOT NULL, responder_id INT DEFAULT NULL, question_id INT DEFAULT NULL, value LONGTEXT NOT NULL, INDEX IDX_339AEF637395ADB (responder_id), UNIQUE INDEX UNIQ_339AEF61E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE form (id INT AUTO_INCREMENT NOT NULL, admin_id INT DEFAULT NULL, name LONGTEXT NOT NULL, INDEX IDX_5288FD4F642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, email LONGTEXT NOT NULL, password LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE responder (id INT AUTO_INCREMENT NOT NULL, admin_id INT DEFAULT NULL, name LONGTEXT NOT NULL, email LONGTEXT NOT NULL, INDEX IDX_5F311AF7642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mood ADD CONSTRAINT FK_339AEF637395ADB FOREIGN KEY (responder_id) REFERENCES responder (id)');
        $this->addSql('ALTER TABLE mood ADD CONSTRAINT FK_339AEF61E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE form ADD CONSTRAINT FK_5288FD4F642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE responder ADD CONSTRAINT FK_5F311AF7642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE question ADD form_id INT DEFAULT NULL, ADD question_number LONGTEXT NOT NULL, ADD name LONGTEXT NOT NULL, ADD answers JSON NOT NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E5FF69B7D FOREIGN KEY (form_id) REFERENCES form (id)');
        $this->addSql('CREATE INDEX IDX_B6F7494E5FF69B7D ON question (form_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E5FF69B7D');
        $this->addSql('ALTER TABLE form DROP FOREIGN KEY FK_5288FD4F642B8210');
        $this->addSql('ALTER TABLE responder DROP FOREIGN KEY FK_5F311AF7642B8210');
        $this->addSql('ALTER TABLE mood DROP FOREIGN KEY FK_339AEF637395ADB');
        $this->addSql('DROP TABLE admin_role');
        $this->addSql('DROP TABLE mood');
        $this->addSql('DROP TABLE form');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE responder');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP INDEX IDX_B6F7494E5FF69B7D ON question');
        $this->addSql('ALTER TABLE question DROP form_id, DROP question_number, DROP name, DROP answers');
    }
}
