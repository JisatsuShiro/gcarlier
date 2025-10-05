<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter la table ssh_attempt
 */
final class Version20251005211800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la table ssh_attempt pour le monitoring des connexions SSH';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ssh_attempt (
            id INT AUTO_INCREMENT NOT NULL, 
            server_id INT DEFAULT NULL, 
            ip_address VARCHAR(45) NOT NULL, 
            username VARCHAR(50) DEFAULT NULL, 
            success TINYINT(1) NOT NULL, 
            attempted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
            port VARCHAR(100) DEFAULT NULL, 
            method LONGTEXT DEFAULT NULL, 
            country VARCHAR(255) DEFAULT NULL, 
            raw_log LONGTEXT DEFAULT NULL, 
            INDEX IDX_F5E7E0D51844E6B7 (server_id), 
            INDEX idx_attempted_at (attempted_at), 
            INDEX idx_success (success), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE ssh_attempt ADD CONSTRAINT FK_F5E7E0D51844E6B7 FOREIGN KEY (server_id) REFERENCES vps_server (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ssh_attempt DROP FOREIGN KEY FK_F5E7E0D51844E6B7');
        $this->addSql('DROP TABLE ssh_attempt');
    }
}
