<?php

class ClientSpouse extends Connection
{
    private $table = 'tbl_client_spouse';
    public $pk = 'spouse_id';
    public $name = 'spouse_name';
    public $fk = 'client_id';

    public $inputs;

    public function addOrUpdate()
    {
        $fk = $this->clean($this->inputs[$this->fk]);
        $spouse_name = $this->clean($this->inputs['spouse_name']);
        $spouse_residence = $this->clean($this->inputs['spouse_residence']);
        $spouse_res_cert_no = $this->clean($this->inputs['spouse_res_cert_no']);
        $spouse_res_cert_issued_at = $this->clean($this->inputs['spouse_res_cert_issued_at']);
        $spouse_res_cert_date = $this->clean($this->inputs['spouse_res_cert_date']);
        $spouse_employer = $this->clean($this->inputs['spouse_employer']);
        $spouse_employer_address = $this->clean($this->inputs['spouse_employer_address']);
        $spouse_employer_contact = $this->clean($this->inputs['spouse_employer_contact']);
        $spouse_employment_position = $this->clean($this->inputs['spouse_employment_position']);
        $spouse_employment_status = $this->clean($this->inputs['spouse_employment_status']);
        $spouse_employment_length = $this->clean($this->inputs['spouse_employment_length']);
        $spouse_employment_income = $this->clean($this->inputs['spouse_employment_income']);
        $spouse_last_employment = $this->clean($this->inputs['spouse_last_employment']);

        $is_exist = $this->select($this->table, $this->pk, "client_id = '$fk'");

        $form = array(
            'client_id'                 => $fk,
            'spouse_name'               => $spouse_name,
            'spouse_residence'          => $spouse_residence,
            'spouse_res_cert_no'        => $spouse_res_cert_no,
            'spouse_res_cert_issued_at' => $spouse_res_cert_issued_at,
            'spouse_res_cert_date'      => $spouse_res_cert_date,
            'spouse_employer'           => $spouse_employer,
            'spouse_employer_address'   => $spouse_employer_address,
            'spouse_employer_contact'   => $spouse_employer_contact,
            'spouse_employment_position' => $spouse_employment_position,
            'spouse_employment_status'  => $spouse_employment_status,
            'spouse_employment_length'  => $spouse_employment_length,
            'spouse_employment_income'  => $spouse_employment_income,
            'spouse_last_employment'    => $spouse_last_employment,
        );

        return $is_exist->num_rows > 0 ? $this->update($this->table, $form, "client_id = '$fk'") : $this->insert($this->table, $form);
    }

    public function view()
    {
        $client_id = $this->inputs['client_id'];
        $result = $this->select($this->table, "*", "client_id = '$client_id'");

        if ($result->num_rows < 1)
            return array(
                'spouse_id'                 => '',
                'spouse_name'               => '',
                'spouse_residence'          => '',
                'spouse_res_cert_no'        => '',
                'spouse_res_cert_issued_at' => '',
                'spouse_res_cert_date'      => '',
                'spouse_employer'           => '',
                'spouse_employer_address'   => '',
                'spouse_employer_contact'   => '',
                'spouse_employment_position' => '',
                'spouse_employment_status'  => '',
                'spouse_employment_length'  => '',
                'spouse_employment_income'  => '',
                'spouse_last_employment'    => '',
            );

        $row = $result->fetch_assoc();
        return $row;
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
                $this->metadata($this->name, 'varchar', 75),
                $this->metadata('spouse_residence', 'varchar', 150),
                $this->metadata('spouse_res_cert_no', 'varchar', 50),
                $this->metadata('spouse_res_cert_issued_at', 'varchar', 150),
                $this->metadata('spouse_res_cert_date', 'date', '', 'NULL'),
                $this->metadata('spouse_employer', 'varchar', 150),
                $this->metadata('spouse_employer_address', 'varchar', 150),
                $this->metadata('spouse_employer_contact', 'varchar', 50),
                $this->metadata('spouse_employment_position', 'varchar', 50),
                $this->metadata('spouse_employment_status', 'varchar', 10),
                $this->metadata('spouse_employment_length', 'varchar', 10),
                $this->metadata('spouse_employment_income', 'DECIMAL', '15,3'),
                $this->metadata('spouse_last_employment', 'varchar', 50),
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
