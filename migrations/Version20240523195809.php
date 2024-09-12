<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240523195809 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE articlejobtimeentry (id INT AUTO_INCREMENT NOT NULL, technician_id INT DEFAULT NULL, job_type_id INT DEFAULT NULL, start DATETIME NOT NULL, end DATETIME DEFAULT NULL, INDEX IDX_5600DCC4E6C5D496 (technician_id), INDEX IDX_5600DCC45FA33B08 (job_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE articlejobtimeentry ADD CONSTRAINT FK_5600DCC4E6C5D496 FOREIGN KEY (technician_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE articlejobtimeentry ADD CONSTRAINT FK_5600DCC45FA33B08 FOREIGN KEY (job_type_id) REFERENCES jobtype (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articlejobtimeentry DROP FOREIGN KEY FK_5600DCC4E6C5D496');
        $this->addSql('ALTER TABLE articlejobtimeentry DROP FOREIGN KEY FK_5600DCC45FA33B08');
        $this->addSql('DROP TABLE articlejobtimeentry');
    }
}
