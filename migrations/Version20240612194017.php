<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612194017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE club (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, postal_code VARCHAR(5) NOT NULL, city VARCHAR(255) NOT NULL, phone VARCHAR(10) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE club_discipline (club_id INT NOT NULL, discipline_id INT NOT NULL, INDEX IDX_3C78A0D261190A32 (club_id), INDEX IDX_3C78A0D2A5522701 (discipline_id), PRIMARY KEY(club_id, discipline_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE discipline (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE club_discipline ADD CONSTRAINT FK_3C78A0D261190A32 FOREIGN KEY (club_id) REFERENCES club (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE club_discipline ADD CONSTRAINT FK_3C78A0D2A5522701 FOREIGN KEY (discipline_id) REFERENCES discipline (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club_discipline DROP FOREIGN KEY FK_3C78A0D261190A32');
        $this->addSql('ALTER TABLE club_discipline DROP FOREIGN KEY FK_3C78A0D2A5522701');
        $this->addSql('DROP TABLE club');
        $this->addSql('DROP TABLE club_discipline');
        $this->addSql('DROP TABLE discipline');
        $this->addSql('DROP TABLE `user`');
    }
}
