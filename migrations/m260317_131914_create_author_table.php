<?php

use yii\db\Migration;

class m260317_131914_create_author_table extends Migration {
    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->createTable('author', [
            'id' => $this->primaryKey()->unsigned(),
            'fio' => $this->string(255)->notNull(),
            'created' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated' => $this->timestamp()->null()->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropTable('author');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260316_171914_create_author_tabls cannot be reverted.\n";

        return false;
    }
    */
}
