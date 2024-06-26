<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240626083543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club ADD last_update DATETIME DEFAULT NULL, ADD creation_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE user ADD registration_date DATETIME NOT NULL, ADD last_login_date DATETIME DEFAULT NULL, CHANGE name name VARCHAR(100) DEFAULT NULL, CHANGE firstname firstname VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP last_update, DROP creation_date');
        $this->addSql('ALTER TABLE `user` DROP registration_date, DROP last_login_date, CHANGE name name VARCHAR(100) NOT NULL, CHANGE firstname firstname VARCHAR(100) NOT NULL');
    }
}
