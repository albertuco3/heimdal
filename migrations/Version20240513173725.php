<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240513173725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, receiver_id INT DEFAULT NULL, job_type_id INT DEFAULT NULL, code VARCHAR(255) DEFAULT NULL, parked TINYINT(1) DEFAULT 0, customer VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, serial_number VARCHAR(255) DEFAULT NULL, delivery_note_id VARCHAR(255) DEFAULT NULL, INDEX IDX_23A0E667E3C61F9 (owner_id), INDEX IDX_23A0E66CD53EDB6 (receiver_id), INDEX IDX_23A0E665FA33B08 (job_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jobtype (id INT AUTO_INCREMENT NOT NULL, finishes TINYINT(1) NOT NULL, description VARCHAR(255) DEFAULT NULL, points_per_completion NUMERIC(10, 0) DEFAULT \'0\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE movement (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, responsible_id INT DEFAULT NULL, new_owner INT DEFAULT NULL, old_owner INT DEFAULT NULL, new_receiver INT DEFAULT NULL, old_receiver INT DEFAULT NULL, new_job_type INT DEFAULT NULL, old_job_type INT DEFAULT NULL, date DATETIME NOT NULL, INDEX IDX_F4DD95F77294869C (article_id), INDEX IDX_F4DD95F7602AD315 (responsible_id), INDEX IDX_F4DD95F737F6A5A1 (new_owner), INDEX IDX_F4DD95F769F42C9A (old_owner), INDEX IDX_F4DD95F77AD100BA (new_receiver), INDEX IDX_F4DD95F756A048BB (old_receiver), INDEX IDX_F4DD95F74C7BAD44 (new_job_type), INDEX IDX_F4DD95F7600AE545 (old_job_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E667E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E665FA33B08 FOREIGN KEY (job_type_id) REFERENCES jobtype (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F77294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F7602AD315 FOREIGN KEY (responsible_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F737F6A5A1 FOREIGN KEY (new_owner) REFERENCES user (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F769F42C9A FOREIGN KEY (old_owner) REFERENCES user (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F77AD100BA FOREIGN KEY (new_receiver) REFERENCES user (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F756A048BB FOREIGN KEY (old_receiver) REFERENCES user (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F74C7BAD44 FOREIGN KEY (new_job_type) REFERENCES jobtype (id)');
        $this->addSql('ALTER TABLE movement ADD CONSTRAINT FK_F4DD95F7600AE545 FOREIGN KEY (old_job_type) REFERENCES jobtype (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E667E3C61F9');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66CD53EDB6');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E665FA33B08');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F77294869C');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F7602AD315');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F737F6A5A1');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F769F42C9A');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F77AD100BA');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F756A048BB');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F74C7BAD44');
        $this->addSql('ALTER TABLE movement DROP FOREIGN KEY FK_F4DD95F7600AE545');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE jobtype');
        $this->addSql('DROP TABLE movement');
        $this->addSql('DROP TABLE user');
    }
}
