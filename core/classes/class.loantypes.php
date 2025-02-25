<?php

class LoanTypes extends Connection
{
    private $table = 'tbl_loan_types';
    public $pk = 'loan_type_id';
    public $name = 'loan_type';

    public $inputs;

    public function add()
    {
        $fixed_interest = (!isset($this->inputs['fixed_interest']) ? "" : "Y");
        $form = array(
            $this->name             => $this->clean($this->inputs[$this->name]),
            'loan_type_interest'    => $this->clean($this->inputs['loan_type_interest']),
            'penalty_percentage'    => $this->clean($this->inputs['penalty_percentage']),
            'remarks'               => $this->clean($this->inputs['remarks']),
            'fixed_interest'        => $this->clean($fixed_interest),
        );

        $response = $this->insertIfNotExist($this->table, $form);
        Logs::action($this->action_response, "LoanTypes", "LoanTypes->add");
        return $response;
    }


    public function edit()
    {
        $name           = $this->clean($this->inputs[$this->name]);
        $fixed_interest = (!isset($this->inputs['fixed_interest']) ? "" : "Y");

        $form = array(
            $this->name             => $name,
            'loan_type_interest'    => $this->clean($this->inputs['loan_type_interest']),
            'penalty_percentage'    => $this->clean($this->inputs['penalty_percentage']),
            'remarks'               => $this->clean($this->inputs['remarks']),
            'fixed_interest'        => $this->clean($fixed_interest),
        );

        $response = $this->updateIfNotExist($this->table, $form);
        Logs::action($this->action_response, "LoanTypes", "LoanTypes->edit");
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

    public function show_fixed()
    {
        $param = isset($this->inputs['param']) ? $this->inputs['param'] : null;
        $rows = array();
        $result = $this->select("tbl_fixed_loan_interest", '*', $param);
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function view($primary_id = null)
    {
        $primary_id = $primary_id == null ? $this->inputs['id'] : $primary_id;
        $result = $this->select($this->table, "*", "$this->pk = '$primary_id'");
        if ($result->num_rows < 1)
            return array(
                'loan_type'             => '',
                'loan_type_interest'    => '',
                'penalty_percentage'    => 0,
                'remarks'               => '',
                'fixed_interest'        => 0,
            );
        return $result->fetch_assoc();
    }

    public function remove()
    {
        foreach ($this->inputs['ids'] as $id) {
            $name = $this->name($id);
            $res = $this->delete($this->table, "$this->pk = '$id'");
            if ($res == 1) {
                Logs::action("Successfuly deleted Loan Type: $name", "Employers", "Employers->remove");
            }
        }
        return 1;
    }

    public function delete_fixed()
    {
        $primary_id = $this->inputs['id'];
        return $this->delete("tbl_fixed_loan_interest", "loan_interest_id = '$primary_id'");
    }

    public function name($primary_id)
    {
        $result = $this->select($this->table, 'loan_type', "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['loan_type'];
        } else {
            return "---";
        }
    }

    public function fixed_status($primary_id)
    {
        $result = $this->select($this->table, 'fixed_interest', "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['fixed_interest'];
        } else {
            return "";
        }
    }


    public function penalty_percentage($primary_id)
    {
        $result = $this->select($this->table, 'penalty_percentage', "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['penalty_percentage'];
        } else {
            return null;
        }
    }

    public function idByName($loan_type)
    {
        $result = $this->select($this->table, 'loan_type_id', "UCASE(loan_type) = UCASE('$loan_type')");

        if ($result->num_rows < 1)
            return 0;

        $row = $result->fetch_assoc();
        return $row['loan_type_id'];
    }


    public function total_per_month($primary_id, $month, $year, $branch_id = null)
    {

        $query = $branch_id == "" ? "" : "AND branch_id='$branch_id'";

        $result = $this->select("tbl_loans", "sum(loan_amount) as total", "MONTH(loan_date) = '$month' AND YEAR(loan_date) = '$year' AND (status = 'R' OR status='F') AND $this->pk = '$primary_id' $query");
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
                $this->metadata($this->name, 'varchar', 50),
                $this->metadata('fixed_interest', 'varchar', 1),
                $this->metadata('loan_type_interest', 'decimal', "5,2"),
                $this->metadata('penalty_percentage', 'decimal', "5,2"),
                $this->metadata('remarks', 'varchar', 250),
                $default['date_added'],
                $default['date_last_modified']
            )
        );

        // TABLE FIXED DETAILS
        $tables[] = array(
            'name'      => "tbl_fixed_loan_interest",
            'primary'   => "loan_interest_id",
            'fields' => array(
                $this->metadata("loan_interest_id", 'int', 11, 'NOT NULL', '', 'AUTO_INCREMENT'),
                $this->metadata("loan_amount", 'decimal', "12,4"),
                $this->metadata('loan_type_id', 'int', 11),
                $this->metadata('interest_amount', 'int', 11),
                $this->metadata('penalty_percentage', 'decimal', "5,2"),
                $this->metadata('interest_terms', 'int', 4),
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
