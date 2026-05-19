<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%book2author}}`.
 */
class m260317_132403_create_book2author_table extends Migration {
    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->createTable('book2author', [
            'id' => $this->primaryKey()->unsigned(),
            'bookId' => $this->integer(10)->unsigned()->notNull(),
            'authorId' => $this->integer(10)->unsigned()->notNull(),
            'created' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('uk_book2author_bookId_authorId', 'book2author', ['bookId', 'authorId'], true);
        $this->addForeignKey('fk_book2author_bookId', 'book2author', 'bookId', 'book', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_book2author_authorId', 'book2author', 'authorId', 'author', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropForeignKey('fk_book2author_bookId', 'book2author');
        $this->dropForeignKey('fk_book2author_authorId', 'book2author');
        $this->dropTable('book2author');
    }
}
