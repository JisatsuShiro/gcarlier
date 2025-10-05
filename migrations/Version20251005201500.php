<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005201500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database schema for VPS Manager';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(100) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_login_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('CREATE TABLE vps_server (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(100) NOT NULL, ip_address VARCHAR(45) NOT NULL, ssh_port INT NOT NULL, ssh_user VARCHAR(50) DEFAULT NULL, location VARCHAR(100) DEFAULT NULL, provider VARCHAR(100) DEFAULT NULL, status VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8B8B5E8A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('CREATE TABLE vps_metric (id INT AUTO_INCREMENT NOT NULL, server_id INT NOT NULL, cpu_usage NUMERIC(5, 2) DEFAULT NULL, memory_usage NUMERIC(5, 2) DEFAULT NULL, disk_usage NUMERIC(5, 2) DEFAULT NULL, network_in BIGINT DEFAULT NULL, network_out BIGINT DEFAULT NULL, uptime INT DEFAULT NULL, recorded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F5E7E0D51844E6B7 (server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE vps_server ADD CONSTRAINT FK_8B8B5E8A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE vps_metric ADD CONSTRAINT FK_F5E7E0D51844E6B7 FOREIGN KEY (server_id) REFERENCES vps_server (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vps_server DROP FOREIGN KEY FK_8B8B5E8A76ED395');
        $this->addSql('ALTER TABLE vps_metric DROP FOREIGN KEY FK_F5E7E0D51844E6B7');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE vps_server');
        $this->addSql('DROP TABLE vps_metric');
    }
}
