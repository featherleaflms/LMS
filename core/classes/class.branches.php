<?php

class Branches extends Connection
{
    private $table = 'tbl_branches';
    public $pk = 'branch_id';
    public $name = 'branch_name';

    public $inputs;

    public function add()
    {
        $form = array(
            $this->name => $this->clean($this->inputs[$this->name]),
            'remarks'   => $this->clean($this->inputs['remarks']),
        );
        $response = $this->insertIfNotExist($this->table, $form);
        Logs::action($this->action_response, "Branches", "Branches->add");
        return $response;
    }

    public function edit()
    {
        $form = array(
            $this->name     => $this->clean($this->inputs[$this->name]),
            'remarks'       => $this->clean($this->inputs['remarks']),
        );

        $response = $this->updateIfNotExist($this->table, $form);
        Logs::action($this->action_response, "Branches", "Branches->edit");
        return $response;
    }

    public function show()
    {
        $param = isset($this->inputs['param']) ? $this->inputs['param'] : null;
        $rows = array();
        $result = $this->select($this->table, '*', $param);
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function view()
    {
        $primary_id = $this->inputs['id'];
        $result = $this->select($this->table, "*", "$this->pk = '$primary_id'");
        return $result->fetch_assoc();
    }

    public function remove()
    {
        foreach ($this->inputs['ids'] as $id) {
            $name = $this->name($id);
            $res = $this->delete($this->table, "$this->pk = '$id'");
            if ($res == 1) {
                Logs::action("Successfuly deleted Branch: $name", "Branches", "Branches->remove");
            }
        }
        return 1;
        // return $this->delete($this->table, "$this->pk IN($ids)");
    }

    public function name($primary_id, $no_branch_text = false)
    {
        $result = $this->select($this->table, 'branch_name', "$this->pk = '$primary_id'");
        if ($result->num_rows < 1)
            return null;

        $row = $result->fetch_assoc();
        return $no_branch_text ? $row['branch_name'] : str_replace(" Branch", "", $row['branch_name']);
    }


    public function penalty_percentage($primary_id)
    {
        $result = $this->select($this->table, 'penalty_percentage', "$this->pk = '$primary_id'");
        $row = $result->fetch_assoc();
        return $row['penalty_percentage'];
    }

    public function total_per_month($primary_id, $month, $year)
    {

        $result = $this->select("tbl_loans", "sum(loan_amount) as total", "MONTH(loan_date) = '$month' AND YEAR(loan_date) = '$year' AND (status = 'R' OR status='F') AND $this->pk = '$primary_id'");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function schema()
    {
        $default['date_added'] = $this->metadata('date_added', 'datetime', '', 'NOT NULL', 'CURRENT_TIMESTAMP');
        $default['date_last_modified'] = $this->metadata('date_last_modified', 'datetime', '', 'NOT NULL', 'CURRENT_TIMESTAMP', 'ON UPDATE CURRENT_TIMESTAMP');
        $default['user_id'] = $this->metadata('user_id', 'int', 11);


        // TABLE HEADER
        $tables[] = array(
            'name'      => $this->table,
            'primary'   => $this->pk,
            'fields' => array(
                $this->metadata($this->pk, 'int', 11, 'NOT NULL', '', 'AUTO_INCREMENT'),
                $this->metadata($this->name, 'varchar', 75),
                $this->metadata('remarks', 'text'),
                $default['user_id'],
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

// CREATE TABLE `tbl_branches` (
//     `branch_id` INT(11) NOT NULL AUTO_INCREMENT,
//     `branch_name` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
//     `remarks` TEXT NOT NULL COLLATE 'latin1_swedish_ci',
//     `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//     `date_last_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     `user_id` INT(11) NOT NULL,
//     PRIMARY KEY (`branch_id`) USING BTREE
// )
// COLLATE='latin1_swedish_ci'
// ENGINE=InnoDB
// AUTO_INCREMENT=3
// ;
