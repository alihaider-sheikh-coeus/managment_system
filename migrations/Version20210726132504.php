<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210726132504 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
//        $this->addSql('ALTER TABLE reviews ADD user_id_id INT NOT NULL, ADD shop_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0F9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reviews ADD CONSTRAINT FK_6970EB0FB852C405 FOREIGN KEY (shop_id_id) REFERENCES shops (id)');
        $this->addSql('CREATE INDEX IDX_6970EB0F9D86650F ON reviews (user_id_id)');
        $this->addSql('CREATE INDEX IDX_6970EB0FB852C405 ON reviews (shop_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0F9D86650F');
        $this->addSql('ALTER TABLE reviews DROP FOREIGN KEY FK_6970EB0FB852C405');
        $this->addSql('DROP INDEX IDX_6970EB0F9D86650F ON reviews');
        $this->addSql('DROP INDEX IDX_6970EB0FB852C405 ON reviews');
        $this->addSql('ALTER TABLE reviews DROP user_id_id, DROP shop_id_id');
    }
}
