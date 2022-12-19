<?php
declare (strict_types = 1);

use Phinx\Migration\AbstractMigration;

final class User extends AbstractMigration
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
        $users = $this->table("users");
        $users->addColumn("username", "string")
            ->addColumn("password", "string")
            ->addColumn("name", "string")
            ->addColumn("balance", "integer")
            ->addColumn("pin", "string")
            ->addTimestamps()
            ->addIndex("username", ["unique" => true])
            ->create();
    }
}
