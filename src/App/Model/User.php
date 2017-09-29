<?php
namespace App\Model;

use \Core\Database\ActiveRecord;

class User extends ActiveRecord
{
    /**
     * name of database table
     * @var string
     */
    protected static $tableName = 'users';

    /**
     * definition of database table fields
     * @var array
     */
    protected static $tableDef = [
        'firstname' => 'varchar(32)',
        'lastname' => 'varchar(32)',
        'email' => 'varchar(255)',
        'changed' => 'datetime',
        'created' => 'datetime',
    ];

    public function beforeSave()
    {
        // current datetime
        $now = date('Y-m-d H:i:s');

        // set creation date for new records
        if (!$this->hasId()) $this->created = $now;

        // set change date
        $this->changed = date('Y-m-d H:i:s');
    }
}