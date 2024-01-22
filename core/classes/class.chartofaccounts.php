<?php

class ChartOfAccounts extends Connection
{
    private $table = 'tbl_chart_of_accounts';
    public $pk = 'chart_id';
    public $name = 'chart_name';

    public $inputs;

    public function add()
    {

        $main_chart_id = (!isset($this->inputs['main_chart_id']) && $this->inputs['chart_type'] == "M" ? "" : $this->clean($this->inputs['main_chart_id']));
        $chart_type = $this->clean($this->inputs['chart_type']);
        if ($chart_type == "S") {
            $chart_class_id = $this->main_chart_class($main_chart_id);
        } else {
            $chart_class_id = $this->clean($this->inputs['chart_class_id']);
        }
        $form = array(
            $this->name         => $this->clean($this->inputs[$this->name]),
            'chart_code'        => $this->clean($this->inputs['chart_code']),
            'chart_type'        => $chart_type,
            'main_chart_id'     => $main_chart_id,
            'chart_class_id'    => $chart_class_id,
        );

        return $this->insertIfNotExist($this->table, $form, "$this->name = '" . $this->inputs[$this->name] . "'");
    }

    public function edit()
    {
        $primary_id = $this->inputs[$this->pk];
        $main_chart_id = (!isset($this->inputs['main_chart_id']) && $this->inputs['chart_type'] == "M" ? "" : $this->clean($this->inputs['main_chart_id']));
        if ($this->inputs['chart_type'] == "S") {
            $chart_class_id = $this->main_chart_class($main_chart_id);
        } else {
            $chart_class_id = $this->clean($this->inputs['chart_class_id']);
        }
        $form = array(
            $this->name         => $this->clean($this->inputs[$this->name]),
            'chart_code'        => $this->clean($this->inputs['chart_code']),
            'chart_type'        => $this->clean($this->inputs['chart_type']),
            'main_chart_id'     => $main_chart_id,
            'chart_class_id'    => $chart_class_id,
        );

        $query =  $this->updateIfNotExist($this->table, $form, "$this->pk = '$primary_id'");
        if ($query) {
            if ($this->inputs['chart_type'] == "M") {
                $form_ = array(
                    'chart_class_id'    => $this->clean($this->inputs['chart_class_id'])
                );

                $this->update($this->table, $form_, "main_chart_id = '$primary_id' AND chart_type='S'");
            }
        }

        return $query;
    }

    public function show()
    {
        $param = isset($this->inputs['param']) ? $this->inputs['param'] : null;
        $rows = array();
        $ChartClassification = new ChartClassification;
        $result = $this->select($this->table, '*', $param);
        while ($row = $result->fetch_assoc()) {
            $main_chart_id  = $row['chart_type'] == "M" ? "" : $row['main_chart_id'];
            $chart_type     = $row['chart_type'] == "M" ? "Main" : "Sub";

            $row['type'] = $chart_type;
            $row['main_chart'] = $this->name($main_chart_id);
            $row['main_chart_id'] = $row['chart_id'];
            $row['credit_method'] = $row['chart_id'];
            $row['chart_class'] = $ChartClassification->name($row['chart_class_id']);
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
        $ids = implode(",", $this->inputs['ids']);

        return $this->delete($this->table, "$this->pk IN($ids)");
    }

    public function name($primary_id)
    {
        $result = $this->select($this->table, 'chart_name', "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['chart_name'];
        } else {
            return '---';
        }
    }

    // public function trial_balance()
    // {
    //     $start_date = $this->inputs['start_date'];
    //     $end_date = $this->inputs['end_date'];
    //     $JournalEntry = new JournalEntry;
    //     $rows = array();
    //     $result = $this->select($this->table, '*');
    //     while ($row = $result->fetch_assoc()) {
    //         $JL = $JournalEntry->total_per_chart($start_date,$end_date,$row['chart_id']);
    //         $sub = $row['chart_type'] == "S" ? "&emsp;&emsp;&emsp; ↪ " : "";
    //         $row['chart_name'] = $sub.$row['chart_name'];
    //         $row['debit'] = number_format($JL['total_debit'],2);//$JL['total_debit'] > 0 ? number_format($JL['total_debit'],2) : "";
    //         $row['credit'] = number_format($JL['total_credit'],2);//$JL['total_credit'] > 0 ? number_format($JL['total_credit'],2) : "";
    //         $rows[] = $row;
    //     }
    //     return $rows;
    // }

    public function trial_balance()
    {
        $start_date = $this->inputs['start_date'];
        $end_date = $this->inputs['end_date'];

        $th_jl = "";
        $th_dc_jl = "";

        $td_jl = "";

        $JournalEntry = new JournalEntry;

        $fetchJournals = $this->select('tbl_journals', '*');
        while ($jlRow = $fetchJournals->fetch_assoc()) {
            if ($jlRow['journal_name'] == "Beginning Balance") {
                $th_jl .= "<th style='color:#fff;'>" . $jlRow['journal_name'] . "</th>";
                $th_dc_jl .= '<th></th>';
            } else {
                $th_jl .= "<th colspan='2' style='color:#fff;border: 1px solid;'>" . $jlRow['journal_name'] . "</th>";
                $th_dc_jl .= '<th style="text-align:right;color:#fff;">Debit</th><th style="text-align:right;color:#fff;">Credit</th>';
            }
        }

        $data = '<table id="dt_entries" class="div1" width="100%" cellspacing="0">
                    <thead style="background: #1f384b;">
                        <tr style="text-align:center;">
                            <td style="background: #1f384b;color:#fff;border: 0px;border-right: 1px solid #bbbbbb;">Chart of Accounts</td>' . $th_jl . '
                            <td colspan="2"></td>
                        </tr>
                        <tr style="background: #607d8b;">
                            <td style="background: #1f384b;border: 0px;border-right: 1px solid #bbbbbb;"></td>' . $th_dc_jl . '
                            <td style="text-align:right;color:#fff;background: #1f384b;">Net Movement</td><td style="text-align:right;color:#fff;background: #1f384b;">Unadjusted Balance</td>
                        </tr>
                    </thead>
                    <tbody>' . $td_jl;

        $tfoot = "<td></td>";
        $result = $this->select($this->table, '*', "chart_type ='M'");
        while ($row = $result->fetch_assoc()) {

            $chart_name = $row['chart_name'];
            $td_ = "<tr><td style=''>" . $chart_name . "</td>";
            $fetchJournals = $this->select('tbl_journals', '*');
            $total_debit = 0;
            $total_credit = 0;
            $total_bb = 0;
            while ($jlRow = $fetchJournals->fetch_assoc()) {
                $JL = $JournalEntry->total_per_chart($start_date, $end_date, $row['chart_id'], $jlRow['journal_id']);
                $debit = $JL['total_debit'] > 0 ? number_format($JL['total_debit'], 2) : "";
                $credit = $JL['total_credit'] > 0 ? number_format($JL['total_credit'], 2) : "";

                if ($jlRow['journal_name'] == "Beginning Balance") {
                    $bb_total = $JL['total_debit'] - $JL['total_credit'];
                    $total_bb = $bb_total;

                    $td_ .= "<td style='text-align:right;'>" . number_format($bb_total, 2) . "</td>";
                } else {
                    $td_ .= "<td style='text-align:right;'>" . $debit . "</td><td style='text-align:right;'>" . $credit . "</td>";
                }

                $total_debit += $JL['total_debit'];
                $total_credit += $JL['total_credit'];
            }

            $net_movement = $total_debit - $total_credit;
            $unadjusted_movement = $total_bb - $net_movement;
            $td_ .= "<td style='text-align:right;'>" . $net_movement . "</td><td style='text-align:right;'>" . $unadjusted_movement . "</td></tr>";
            $data .= $td_;

            $fetchSub = $this->select($this->table, '*', "chart_type ='S' AND main_chart_id='$row[chart_id]'");
            while ($subRow = $fetchSub->fetch_assoc()) {
                $chart_name = "&emsp; <i class='fas fa-arrow-right'></i> " . $subRow['chart_name'];
                $td_ = "<tr><td style=''>" . $chart_name . "</td>";
                $fetchJournals = $this->select('tbl_journals', '*');
                $total_debit = 0;
                $total_credit = 0;
                $total_bb = 0;
                while ($jlRow = $fetchJournals->fetch_assoc()) {
                    $JL = $JournalEntry->total_per_chart($start_date, $end_date, $subRow['chart_id'], $jlRow['journal_id']);
                    $debit = $JL['total_debit'] > 0 ? number_format($JL['total_debit'], 2) : "";
                    $credit = $JL['total_credit'] > 0 ? number_format($JL['total_credit'], 2) : "";

                    if ($jlRow['journal_name'] == "Beginning Balance") {
                        $bb_total = $JL['total_debit'] - $JL['total_credit'];
                        $total_bb = $bb_total;

                        $td_ .= "<td style='text-align:right;'>" . number_format($bb_total, 2) . "</td>";
                    } else {
                        $td_ .= "<td style='text-align:right;'>" . $debit . "</td><td style='text-align:right;'>" . $credit . "</td>";
                    }

                    $total_debit += $JL['total_debit'];
                    $total_credit += $JL['total_credit'];
                }

                $net_movement = $total_debit - $total_credit;
                $unadjusted_movement = $total_bb - $net_movement;
                $td_ .= "<td style='text-align:right;'>" . $net_movement . "</td><td style='text-align:right;'>" . $unadjusted_movement . "</td></tr>";
                $data .= $td_;
            }
        }



        $data .= "</tbody><tfoot style='text-align:right;background: #1f384b;color:#fff;'></tfoot></table>";
        echo $data;
    }

    public function main_chart_class($primary_id)
    {
        $result = $this->select($this->table, "chart_class_id", "$this->pk = '$primary_id'");
        $row = $result->fetch_assoc();
        return $row['chart_class_id'];
    }

    public function chart_data($code, $to_replace = true)
    {
        $code = $to_replace ? strtoupper(str_replace(" - ", " ", $code)) : $code;
        $param = $to_replace ? "UCASE(REPLACE(chart_name,' - ',' ')) LIKE '%$code%'" : "chart_name like '%$code%'";
        $result = $this->select($this->table, '*', $param);
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return 0;
        }
    }

    public function idByName($name)
    {
        $result = $this->select($this->table, $this->pk, "UCASE($this->name) = UCASE('$name')");

        if ($result->num_rows < 1)
            return 0;

        $row = $result->fetch_assoc();
        return $row[$this->pk];
    }

    public function import()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $response = [];
        $file = $_FILES['csv_file'];
        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($fileType != 'csv') {
            $response['status'] = -1;
            $response['text'] = 'Invalid file format. Only CSV files are allowed.';
            return $response;
        }

        // Read the CSV file data
        $csvData = array();
        if (($handle = fopen($file['tmp_name'], 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $csvData[] = $row;
            }
            fclose($handle);
        } else {
            $response['status'] = -1;
            $response['text'] = 'Failed to read the CSV file.';
            return $response;
        }

        // Display the processed data
        $branches = ["BCD" => 1, "LC" => 2];
        $insurance_data = [];
        $count = 0;
        $success_import = 0;
        $unsuccess_import = 0;
        $ChartClassification = new ChartClassification;
        foreach ($csvData as $row) {
            if ($count > 0) {
                $form = [
                    'chart_code'    => $row[0],
                    'chart_type'    => $row[1],
                    'chart_class_id'    => $row[2], // Chart Class ID
                    'chart_name' => $row[4],
                ];

                if ($row[1] == 'M') {
                    $form['chart_class_id'] = $ChartClassification->idByName($this->clean($row[2]));
                } else {
                    $form['main_chart_id'] = $this->idByName($this->clean($row[3]));
                }

                $ChartOfAccounts = new ChartOfAccounts;
                $ChartOfAccounts->inputs = $form;
                $chart_id = $row[1] != '' ? $ChartOfAccounts->add() : 0;

                if ($chart_id == 2) {
                    $form['import_status'] = 0;
                    $unsuccess_import += 1;
                } else if ($chart_id == 0) {
                    $form['import_status'] = 0;
                    $unsuccess_import += 1;
                } else {
                    $form['import_status'] = 1;
                    $success_import += 1;
                }

                $insurance_data[] = $form;
            }
            $count++;
        }
        $response['status'] = 1;
        $response['insurances'] = $insurance_data;
        $response['success_import'] = $success_import;
        $response['unsuccess_import'] = $unsuccess_import;
        return $response;
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
                $this->metadata('chart_code', 'varchar', 10),
                $this->metadata('chart_type', 'varchar', 1),
                $this->metadata('main_chart_id', 'int', 11),
                $this->metadata('chart_class_id', 'int', 11),
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

// CREATE TABLE `tbl_chart_of_accounts` (
//     `chart_id` INT(11) NOT NULL AUTO_INCREMENT,
//     `chart_code` VARCHAR(10) NOT NULL COLLATE 'latin1_swedish_ci',
//     `chart_name` VARCHAR(50) NOT NULL COLLATE 'latin1_swedish_ci',
//     `chart_type` VARCHAR(1) NOT NULL COMMENT 'M - Main; S - Sub' COLLATE 'latin1_swedish_ci',
//     `main_chart_id` INT(11) NULL DEFAULT NULL,
//     `chart_class_id` INT(11) NOT NULL,
//     `date_added` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
//     `date_last_modified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//     `user_id` INT(11) NOT NULL,
//     PRIMARY KEY (`chart_id`) USING BTREE
// )
// COLLATE='latin1_swedish_ci'
// ENGINE=InnoDB
// AUTO_INCREMENT=42
// ;
