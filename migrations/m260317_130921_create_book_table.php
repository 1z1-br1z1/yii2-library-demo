<?php

use yii\db\Migration;

class m260317_130921_create_book_table extends Migration {
    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->createTable('book', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'note' => $this->text()->notNull(),
            'year' => $this->integer(4)->notNull(),
            'isbn' => $this->string(13)->notNull(),
            'photo' => $this->string(255),
            'created' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated' => $this->timestamp()->null()->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('uk_book_isbn', 'book', ['isbn'], true);
        $this->createIndex('ik_book_year', 'book', ['year']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropTable('book');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260316_170921_create_books_tabls cannot be reverted.\n";

        return false;
    }
    */
}
