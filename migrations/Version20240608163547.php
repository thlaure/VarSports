<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240608163547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE club_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE discipline_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE club (id INT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(5) NOT NULL, city VARCHAR(255) NOT NULL, phone VARCHAR(10) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE club_discipline (club_id INT NOT NULL, discipline_id INT NOT NULL, PRIMARY KEY(club_id, discipline_id))');
        $this->addSql('CREATE INDEX IDX_3C78A0D261190A32 ON club_discipline (club_id)');
        $this->addSql('CREATE INDEX IDX_3C78A0D2A5522701 ON club_discipline (discipline_id)');
        $this->addSql('CREATE TABLE discipline (id INT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE club_discipline ADD CONSTRAINT FK_3C78A0D261190A32 FOREIGN KEY (club_id) REFERENCES club (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE club_discipline ADD CONSTRAINT FK_3C78A0D2A5522701 FOREIGN KEY (discipline_id) REFERENCES discipline (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE club_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE discipline_id_seq CASCADE');
        $this->addSql('ALTER TABLE club_discipline DROP CONSTRAINT FK_3C78A0D261190A32');
        $this->addSql('ALTER TABLE club_discipline DROP CONSTRAINT FK_3C78A0D2A5522701');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE club_discipline');
        $this->addSql('DROP TABLE discipline');
    }
}
