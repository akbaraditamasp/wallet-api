<?php
declare (strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class AdminLogin extends AbstractMigration
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
        $table = $this->table("admin_logins");
        $table->addColumn("cuid", "string")
            ->addColumn("admin_id", "integer", ["signed" => false, "null" => false])
            ->addTimestamps()
            ->addForeignKey('admin_id', 'admins', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex("cuid", ["unique" => true])
            ->create();
    }
}
