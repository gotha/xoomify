<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240121074047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE artist_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE artist_image_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE track_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_play_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_token_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE artist (id INT NOT NULL, spotify_id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE artist_image (id INT NOT NULL, artist_id INT NOT NULL, height INT NOT NULL, width INT NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A531340CB7970CF8 ON artist_image (artist_id)');
        $this->addSql('CREATE TABLE track (id INT NOT NULL, spotify_id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, duration_ms INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE track_artist (track_id INT NOT NULL, artist_id INT NOT NULL, PRIMARY KEY(track_id, artist_id))');
        $this->addSql('CREATE INDEX IDX_499B576E5ED23C43 ON track_artist (track_id)');
        $this->addSql('CREATE INDEX IDX_499B576EB7970CF8 ON track_artist (artist_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, user_type VARCHAR(255) DEFAULT NULL, spotify_user_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_play_history (id INT NOT NULL, track_id INT NOT NULL, user_id INT NOT NULL, played_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D1686BF95ED23C43 ON user_play_history (track_id)');
        $this->addSql('CREATE INDEX IDX_D1686BF9A76ED395 ON user_play_history (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq ON user_play_history (track_id, user_id, played_at)');
        $this->addSql('CREATE TABLE user_token (id INT NOT NULL, user_id INT DEFAULT NULL, access_token VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, expires_in INT DEFAULT NULL, refresh_token VARCHAR(255) DEFAULT NULL, date_created TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDF55A63A76ED395 ON user_token (user_id)');
        $this->addSql('ALTER TABLE artist_image ADD CONSTRAINT FK_A531340CB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE track_artist ADD CONSTRAINT FK_499B576E5ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE track_artist ADD CONSTRAINT FK_499B576EB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_play_history ADD CONSTRAINT FK_D1686BF95ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_play_history ADD CONSTRAINT FK_D1686BF9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A63A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE artist_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE artist_image_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE track_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE user_play_history_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_token_id_seq CASCADE');
        $this->addSql('ALTER TABLE artist_image DROP CONSTRAINT FK_A531340CB7970CF8');
        $this->addSql('ALTER TABLE track_artist DROP CONSTRAINT FK_499B576E5ED23C43');
        $this->addSql('ALTER TABLE track_artist DROP CONSTRAINT FK_499B576EB7970CF8');
        $this->addSql('ALTER TABLE user_play_history DROP CONSTRAINT FK_D1686BF95ED23C43');
        $this->addSql('ALTER TABLE user_play_history DROP CONSTRAINT FK_D1686BF9A76ED395');
        $this->addSql('ALTER TABLE user_token DROP CONSTRAINT FK_BDF55A63A76ED395');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE artist_image');
        $this->addSql('DROP TABLE track');
        $this->addSql('DROP TABLE track_artist');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_play_history');
        $this->addSql('DROP TABLE user_token');
    }
}
