<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240523221008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articlejobtimeentry ADD article_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE articlejobtimeentry ADD CONSTRAINT FK_5600DCC47294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('CREATE INDEX IDX_5600DCC47294869C ON articlejobtimeentry (article_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articlejobtimeentry DROP FOREIGN KEY FK_5600DCC47294869C');
        $this->addSql('DROP INDEX IDX_5600DCC47294869C ON articlejobtimeentry');
        $this->addSql('ALTER TABLE articlejobtimeentry DROP article_id');
    }
}
