<?php

use Phinx\Seed\AbstractSeed;

class AdminDefault extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $data = [
            "username" => "admin",
            "password" => password_hash("123456", PASSWORD_BCRYPT),
        ];

        $table = $this->table("admins");
        $table->insert($data)->saveData();
    }
}
