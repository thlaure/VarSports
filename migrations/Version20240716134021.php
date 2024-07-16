<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240716134021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP INDEX UNIQ_B8EE387240BE1047, ADD INDEX IDX_B8EE387240BE1047 (postal_codefk_id)');
        $this->addSql('ALTER TABLE club DROP INDEX UNIQ_B8EE38721B1C54A3, ADD INDEX IDX_B8EE38721B1C54A3 (cityfk_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP INDEX IDX_B8EE38721B1C54A3, ADD UNIQUE INDEX UNIQ_B8EE38721B1C54A3 (cityfk_id)');
        $this->addSql('ALTER TABLE club DROP INDEX IDX_B8EE387240BE1047, ADD UNIQUE INDEX UNIQ_B8EE387240BE1047 (postal_codefk_id)');
    }
}
