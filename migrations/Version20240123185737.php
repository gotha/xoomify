<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240123185737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE user_image_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_image (id INT NOT NULL, user_id INT NOT NULL, url TEXT NOT NULL, width INT NOT NULL, height INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_27FFFF07A76ED395 ON user_image (user_id)');
        $this->addSql('ALTER TABLE user_image ADD CONSTRAINT FK_27FFFF07A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE user_image_id_seq CASCADE');
        $this->addSql('ALTER TABLE user_image DROP CONSTRAINT FK_27FFFF07A76ED395');
        $this->addSql('DROP TABLE user_image');
    }
}
