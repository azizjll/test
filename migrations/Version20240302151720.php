<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240302151720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD email VARCHAR(180) NOT NULL, ADD roles JSON NOT NULL, ADD reset_token VARCHAR(100) NOT NULL, ADD password VARCHAR(255) NOT NULL, ADD is_verified TINYINT(1) NOT NULL, ADD date_naissance DATETIME NOT NULL, ADD numero VARCHAR(255) NOT NULL, ADD cin INT DEFAULT NULL, ADD etat TINYINT(1) DEFAULT NULL, ADD image_url VARCHAR(255) NOT NULL, ADD borchure_filename VARCHAR(255) DEFAULT NULL, CHANGE nom username VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74 ON user');
        $this->addSql('ALTER TABLE user ADD nom VARCHAR(255) NOT NULL, DROP username, DROP email, DROP roles, DROP reset_token, DROP password, DROP is_verified, DROP date_naissance, DROP numero, DROP cin, DROP etat, DROP image_url, DROP borchure_filename');
    }
}
