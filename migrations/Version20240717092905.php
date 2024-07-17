<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240717092905 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE38721B1C54A3');
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE387240BE1047');
        $this->addSql('DROP INDEX IDX_B8EE38721B1C54A3 ON club');
        $this->addSql('DROP INDEX IDX_B8EE387240BE1047 ON club');
        $this->addSql('ALTER TABLE club ADD city_id INT DEFAULT NULL, ADD postal_code_id INT DEFAULT NULL, DROP cityfk_id, DROP postal_codefk_id, CHANGE postal_code postal_code_code VARCHAR(5) DEFAULT NULL, CHANGE city city_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE38728BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE3872BDBA6A61 FOREIGN KEY (postal_code_id) REFERENCES postal_code (id)');
        $this->addSql('CREATE INDEX IDX_B8EE38728BAC62AF ON club (city_id)');
        $this->addSql('CREATE INDEX IDX_B8EE3872BDBA6A61 ON club (postal_code_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE38728BAC62AF');
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE3872BDBA6A61');
        $this->addSql('DROP INDEX IDX_B8EE38728BAC62AF ON club');
        $this->addSql('DROP INDEX IDX_B8EE3872BDBA6A61 ON club');
        $this->addSql('ALTER TABLE club ADD cityfk_id INT DEFAULT NULL, ADD postal_codefk_id INT DEFAULT NULL, DROP city_id, DROP postal_code_id, CHANGE postal_code_code postal_code VARCHAR(5) DEFAULT NULL, CHANGE city_name city VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE38721B1C54A3 FOREIGN KEY (cityfk_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE387240BE1047 FOREIGN KEY (postal_codefk_id) REFERENCES postal_code (id)');
        $this->addSql('CREATE INDEX IDX_B8EE38721B1C54A3 ON club (cityfk_id)');
        $this->addSql('CREATE INDEX IDX_B8EE387240BE1047 ON club (postal_codefk_id)');
    }
}
