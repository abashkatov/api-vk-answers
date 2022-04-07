<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220407202543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer ADD is_best BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE question ADD best_answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB6DEA817 FOREIGN KEY (best_answer_id) REFERENCES answer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6F7494EB6DEA817 ON question (best_answer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494EB6DEA817');
        $this->addSql('DROP INDEX UNIQ_B6F7494EB6DEA817');
        $this->addSql('ALTER TABLE question DROP best_answer_id');
        $this->addSql('ALTER TABLE answer DROP is_best');
    }
}
