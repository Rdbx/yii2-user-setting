<?php

use yii\db\Migration;

/**
 * Class m150227_114524_init
 */
class m000000_000000_init_redbox_personal_settings extends Migration
{
    /**
     * This method contains the logic to be executed when applying this migration.
     */
    public function up()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions
                = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%personal_settings}}', [
            'id'          => $this->primaryKey(),
            'user_id'     => $this->integer()->unsigned(),
            'type'        => $this->string(10)->notNull(),
            'section'     => $this->string()->notNull(),
            'key'         => $this->string()->notNull(),
            'value'       => $this->text()->notNull(),
            'status'      => $this->smallInteger()->notNull()->defaultValue(1),
            'description' => $this->string(),
            'created_at'  => $this->dateTime()->notNull(),
            'updated_at'  => $this->dateTime()->notNull(),
        ], $tableOptions);
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     */
    public function down()
    {
        $this->dropTable('{{%personal_settings}}');
    }
}
