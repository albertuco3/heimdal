<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240514105835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE jobtypetransition (id INT AUTO_INCREMENT NOT NULL, from_job_type_id INT NOT NULL, to_job_type_id INT NOT NULL, points_per_completion INT NOT NULL, INDEX IDX_AC159B5C1EC0A6D (from_job_type_id), INDEX IDX_AC159B57130056B (to_job_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE jobtypetransition ADD CONSTRAINT FK_AC159B5C1EC0A6D FOREIGN KEY (from_job_type_id) REFERENCES jobtype (id)');
        $this->addSql('ALTER TABLE jobtypetransition ADD CONSTRAINT FK_AC159B57130056B FOREIGN KEY (to_job_type_id) REFERENCES jobtype (id)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jobtypetransition DROP FOREIGN KEY FK_AC159B5C1EC0A6D');
        $this->addSql('ALTER TABLE jobtypetransition DROP FOREIGN KEY FK_AC159B57130056B');
        $this->addSql('DROP TABLE jobtypetransition');
    }
}
