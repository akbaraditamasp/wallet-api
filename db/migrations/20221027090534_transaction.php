<?php
declare (strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class Transaction extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table("transactions");
        $table->addColumn("type", "enum", ["values" => ["in", "out"]])
            ->addColumn("title", "string")
            ->addColumn("note", "string")
            ->addColumn("user_id", "integer", ["signed" => false, "null" => false])
            ->addColumn("amount", "integer")
            ->addColumn("invoice_id", "string")
            ->addColumn("link", "string")
            ->addColumn("status", "enum", ["values" => ["success", "pending", "failed"]])
            ->addTimestamps()
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
