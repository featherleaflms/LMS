<?php

class UserPrivileges extends Connection
{
    private $table = 'tbl_user_privileges';
    private $pk = 'privilege_id';

    public $inputs;

    public function add()
    {
        $user_category_id = $this->inputs['user_category_id'];
        $loop = $this->inputs;

        $this->update($this->table, ['status' => 0], "user_category_id = '$user_category_id'");
        if (count($loop) > 0) {
            foreach ($loop as $url => $status) {
                if ($url != 'user_category_id') {
                    $result = $this->select($this->table, $this->pk, "user_category_id = '$user_category_id' AND url='$url'");
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $privilege_id = $row['privilege_id'];
                        $this->update($this->table, ['status' => 1], "$this->pk = '$privilege_id'");
                    } else {
                        $this->insert($this->table, [
                            'user_category_id' => $user_category_id,
                            'url' => $url,
                            'status' => 1
                        ]);
                    }
                }
            }
        }
        return 1;
    }

    public function lists()
    {
        $user_category_id = $this->inputs['id'];
        $Menus = new Menus();
        $Menus->lists();

        $master_data = [];
        $_menus = $Menus->menus['master-data'];
        foreach ($_menus as $row) {
            $master_data[] = ['name' => $row['name'], 'url' => $row['url'], 'status' => $this->check($row['url'], $user_category_id)];
        }

        $transaction_data = [];
        $_menus = $Menus->menus['transaction'];
        foreach ($_menus as $row) {
            $transaction_data[] = ['name' => $row['name'], 'url' => $row['url'], 'status' => $this->check($row['url'], $user_category_id)];
        }

        $accounting_data = [];
        $_menus = $Menus->menus['accounting'];
        foreach ($_menus as $row) {
            $accounting_data[] = ['name' => $row['name'], 'url' => $row['url'], 'status' => $this->check($row['url'], $user_category_id)];
        }

        $reports_data = [];
        $_menus = $Menus->menus['report'];
        foreach ($_menus as $row) {
            $reports_data[] = ['name' => $row['name'], 'url' => $row['url'], 'status' => $this->check($row['url'], $user_category_id)];
        }

        $security_data = [];
        $_menus = $Menus->menus['security'];
        foreach ($_menus as $row) {
            $security_data[] = ['name' => $row['name'], 'url' => $row['url'], 'status' => $this->check($row['url'], $user_category_id)];
        }

        return [
            'masterdata' => $master_data,
            'transaction' => $transaction_data,
            'accounting' => $accounting_data,
            'report' => $reports_data,
            'security' => $security_data,
        ];
    }

    public function check($url, $user_category_id)
    {
        $User = new Users();
        $user_category = $User->dataRow($user_category_id, 'user_category');
        if ($user_category == 'A') {
            return 1;
        } else {
            if ($url == 'homepage') {
                return 1;
            } else {
                $result = $this->select($this->table, 'status', "url = '$url' AND user_category_id = '$user_category_id'");
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    return (int) $row['status'];
                } else {
                    return 0;
                }
            }
        }
    }

    public function schema()
    {
        $default['date_added'] = $this->metadata('date_added', 'datetime', '', 'NOT NULL', 'CURRENT_TIMESTAMP');
        $default['date_last_modified'] = $this->metadata('date_last_modified', 'datetime', '', 'NOT NULL', 'CURRENT_TIMESTAMP', 'ON UPDATE CURRENT_TIMESTAMP');


        // TABLE HEADER
        $tables[] = array(
            'name'      => $this->table,
            'primary'   => $this->pk,
            'fields' => array(
                $this->metadata($this->pk, 'int', 11, 'NOT NULL', '', 'AUTO_INCREMENT'),
                $this->metadata('user_category_id', 'int', 11),
                $this->metadata('url', 'varchar', 50),
                $this->metadata('status', 'int', 1),
                $default['date_added'],
                $default['date_last_modified']
            )
        );

        return $this->schemaCreator($tables);
    }

    public function triggers()
    {
        // HEADER
        $triggers[] = array(
            'table' => $this->table,
            'name' => 'delete_' . $this->table,
            'action_time' => 'BEFORE', // ['AFTER','BEFORE']
            'event' => "DELETE", // ['INSERT','UPDATE', 'DELETE']
            "statement" => "INSERT INTO " . $this->table . "_deleted SELECT * FROM $this->table WHERE $this->pk = OLD.$this->pk"
        );
        return $this->triggerCreator($triggers);
    }
}
