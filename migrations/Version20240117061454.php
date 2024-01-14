<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240120090317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artist (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, spotify_id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE track (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, spotify_id VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, duration_ms INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE track_artist (track_id INTEGER NOT NULL, artist_id INTEGER NOT NULL, PRIMARY KEY(track_id, artist_id), CONSTRAINT FK_499B576E5ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_499B576EB7970CF8 FOREIGN KEY (artist_id) REFERENCES artist (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_499B576E5ED23C43 ON track_artist (track_id)');
        $this->addSql('CREATE INDEX IDX_499B576EB7970CF8 ON track_artist (artist_id)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, user_type VARCHAR(255) DEFAULT NULL, spotify_user_id VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE user_play_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, track_id INTEGER NOT NULL, user_id INTEGER NOT NULL, played_at DATETIME NOT NULL, CONSTRAINT FK_D1686BF95ED23C43 FOREIGN KEY (track_id) REFERENCES track (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D1686BF9A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D1686BF95ED23C43 ON user_play_history (track_id)');
        $this->addSql('CREATE INDEX IDX_D1686BF9A76ED395 ON user_play_history (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq ON user_play_history (track_id, user_id, played_at)');
        $this->addSql('CREATE TABLE user_token (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, access_token VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, expires_in INTEGER DEFAULT NULL, refresh_token VARCHAR(255) DEFAULT NULL, date_created DATETIME DEFAULT NULL, CONSTRAINT FK_BDF55A63A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDF55A63A76ED395 ON user_token (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE track');
        $this->addSql('DROP TABLE track_artist');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_play_history');
        $this->addSql('DROP TABLE user_token');
    }
}
