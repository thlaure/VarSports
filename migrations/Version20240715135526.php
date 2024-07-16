<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240715135526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_2D5B0234AE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE postal_code (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(5) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE postal_code_city (postal_code_id INT NOT NULL, city_id INT NOT NULL, INDEX IDX_95B77BB8BDBA6A61 (postal_code_id), INDEX IDX_95B77BB88BAC62AF (city_id), PRIMARY KEY(postal_code_id, city_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B0234AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE postal_code_city ADD CONSTRAINT FK_95B77BB8BDBA6A61 FOREIGN KEY (postal_code_id) REFERENCES postal_code (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE postal_code_city ADD CONSTRAINT FK_95B77BB88BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE club ADD cityfk_id INT DEFAULT NULL, ADD postal_codefk_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE38721B1C54A3 FOREIGN KEY (cityfk_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE387240BE1047 FOREIGN KEY (postal_codefk_id) REFERENCES postal_code (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8EE38721B1C54A3 ON club (cityfk_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8EE387240BE1047 ON club (postal_codefk_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE38721B1C54A3');
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE387240BE1047');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B0234AE80F5DF');
        $this->addSql('ALTER TABLE postal_code_city DROP FOREIGN KEY FK_95B77BB8BDBA6A61');
        $this->addSql('ALTER TABLE postal_code_city DROP FOREIGN KEY FK_95B77BB88BAC62AF');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE postal_code');
        $this->addSql('DROP TABLE postal_code_city');
        $this->addSql('DROP INDEX UNIQ_B8EE38721B1C54A3 ON club');
        $this->addSql('DROP INDEX UNIQ_B8EE387240BE1047 ON club');
        $this->addSql('ALTER TABLE club DROP cityfk_id, DROP postal_codefk_id');
    }
}
