<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251122053212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug field to Product entity and generate slugs for existing products';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, category_id, name, price, description, stock, status FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, description CLOB NOT NULL, stock INTEGER NOT NULL, status VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT "" NOT NULL, CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO product (id, category_id, name, price, description, stock, status, slug) SELECT id, category_id, name, price, description, stock, status, "" FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');

        // Generate slugs for existing products
        $products = $this->connection->fetchAllAssociative('SELECT id, name FROM product');
        foreach ($products as $product) {
            $slug = $this->generateSlug($product['name']);
            $this->addSql('UPDATE product SET slug = ? WHERE id = ?', [$slug, $product['id']]);
        }

        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04AD989D9B62 ON product (slug)');
    }

    /**
     * Generate a URL-friendly slug from a string
     */
    private function generateSlug(string $text): string
    {
        // Convert to lowercase
        $slug = mb_strtolower($text, 'UTF-8');

        // Replace accented characters
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);

        // Remove special characters and replace spaces with hyphens
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);

        // Trim hyphens from ends
        $slug = trim($slug, '-');

        return $slug;
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__product AS SELECT id, category_id, name, price, description, stock, status FROM product');
        $this->addSql('DROP TABLE product');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, description CLOB NOT NULL, stock INTEGER NOT NULL, status VARCHAR(255) NOT NULL, CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO product (id, category_id, name, price, description, stock, status) SELECT id, category_id, name, price, description, stock, status FROM __temp__product');
        $this->addSql('DROP TABLE __temp__product');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
    }
}
