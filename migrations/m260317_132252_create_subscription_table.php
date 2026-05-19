<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subscription}}`.
 */
class m260317_132252_create_subscription_table extends Migration {
    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->createTable('subscription', [
            'id' => $this->primaryKey()->unsigned(),
            'authorId' => $this->integer(10)->unsigned()->notNull(),
            'phone' => $this->char(11)->notNull(),
            'created' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated' => $this->timestamp()->null()->append('ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('uk_subscription_authorId_phone', 'subscription', ['authorId', 'phone'], true);
        $this->addForeignKey('fk_subscription_authorId', 'subscription', 'authorId', 'author', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->dropForeignKey('fk_subscription_authorId', 'subscription');
        $this->dropTable('subscription');
    }
}
