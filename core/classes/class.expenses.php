<?php

class Expenses extends Connection
{
    private $table = 'tbl_expenses';
    public $pk = 'expense_id';
    public $name = 'reference_number';

    private $table_detail = 'tbl_expense_details';
    public $pk2 = 'expense_detail_id';
    public $fk_det = 'chart_id';

    public $inputs;

    public function add()
    {
        $form = array(
            $this->name         => $this->clean($this->inputs[$this->name]),
            'branch_id'         => $this->inputs['branch_id'],
            'expense_date'      => $this->inputs['expense_date'],
            'remarks'           => $this->inputs['remarks'],
            'credit_method'     => $this->inputs['credit_method'],
            'journal_id'        => $this->inputs['journal_id'],
            'status'            => isset($this->inputs['status']) ? $this->inputs['status'] : "S",
            'user_id'           => $_SESSION['lms_user_id']
        );
        return $this->insertIfNotExist($this->table, $form, '', 'Y');
    }

    public function edit()
    {
        $form = array(
            $this->name         => $this->clean($this->inputs[$this->name]),
            'branch_id'         => $this->inputs['branch_id'],
            'expense_date'      => $this->inputs['expense_date'],
            'remarks'           => $this->inputs['remarks'],
            'credit_method'     => $this->inputs['credit_method'],
            'journal_id'        => $this->inputs['journal_id'],
            'user_id'           => $_SESSION['lms_user_id']
        );
        return $this->updateIfNotExist($this->table, $form);
    }

    public function show()
    {
        $Users = new Users;
        $Journals = new Journals;
        $Branches = new Branches;
        $param = isset($this->inputs['param']) ? $this->inputs['param'] : null;
        $rows = array();
        $result = $this->select($this->table, '*', $param);
        while ($row = $result->fetch_assoc()) {
            $row['encoded_by'] = $Users->fullname($row['user_id']);
            $row['journal'] = $Journals->name($row['journal_id']);
            $row['branch_name'] = $Branches->name($row['branch_id']);
            $row['amount'] = number_format($this->total($row['expense_id']), 2);
            $rows[] = $row;
        }
        return $rows;
    }

    function total($primary_id)
    {
        $result = $this->select($this->table_detail, "sum(expense_amount) as total", "$this->pk = '$primary_id'");
        $row = $result->fetch_assoc();

        return  $row['total'];
    }

    public function view()
    {
        $primary_id = $this->inputs['id'];
        $Users = new Users;
        $Journals = new Journals;
        $Branches = new Branches;
        $ChartOfAccounts = new ChartOfAccounts;
        $result = $this->select($this->table, "*", "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $row['encoded_by'] = $Users->fullname($row['user_id']);
            $row['journal_name'] = $Journals->name($row['journal_id']);
            $row['branch_name'] = $Branches->name($row['branch_id']);
            $row['credit_method_name'] = $ChartOfAccounts->name($row['credit_method']);
            return $row;
        } else {
            return null;
        }
    }

    public function remove()
    {
        $ids = implode(",", $this->inputs['ids']);
        return $this->delete($this->table, "$this->pk IN($ids)");
    }

    public function finish()
    {

        $primary_id = $this->inputs['id'];
        $result = $this->select($this->table, "*", "$this->pk = '$primary_id'");
        $row = $result->fetch_assoc();

        if ($row['status'] != "F") {
            $form = array(
                'status' => 'F',
            );
            $sql = $this->update($this->table, $form, "$this->pk = '$primary_id'");

            if ($sql) {

                $Journals = new Journals;
                $code = $Journals->journal_code($row['journal_id']);
                $ref_code = $code . "-" . date('YmdHis');

                $form_journal = array(
                    'reference_number'  => $ref_code,
                    'cross_reference'   => $row['reference_number'],
                    'journal_id'        => $row['journal_id'],
                    'remarks'           => $row['remarks'],
                    'journal_date'      => $row['expense_date'],
                    'user_id'           => $_SESSION['lms_user_id'],
                    'is_manual'         => 'N',
                    'status'            => 'F'
                );

                $journal_entry_id = $this->insert("tbl_journal_entries", $form_journal, 'Y');

                $details = $this->select($this->table_detail, "*", "$this->pk = '$primary_id'");
                $total = 0;
                while ($dRow = $details->fetch_array()) {
                    $total += $dRow['expense_amount'];
                    $form_details = array(
                        'journal_entry_id'      => $journal_entry_id,
                        $this->fk_det           => $dRow['chart_id'],
                        'debit'                 => $dRow['expense_amount'],
                        'credit'                => 0,
                        'description'           => $dRow['expense_desc'],
                    );
                    $this->insert("tbl_journal_entry_details", $form_details);
                }

                $form_credit = array(
                    'journal_entry_id'      => $journal_entry_id,
                    $this->fk_det           => $row['credit_method'],
                    'debit'                 => 0,
                    'credit'                => $total
                );
                $this->insert("tbl_journal_entry_details", $form_credit);
            }

            return $sql;
        } else {
            return -2;
        }
    }

    public function pk_by_name($name = null)
    {
        $name = $name == null ? $this->inputs[$this->name] : $name;
        $result = $this->select($this->table, $this->pk, "$this->name = '$name' AND (status='F' OR status='P')");
        $row = $result->fetch_assoc();
        return $row[$this->pk] * 1;
    }

    public function pk_name($name, $customer_id)
    {
        $result = $this->select($this->table, $this->pk, "$this->name = '$name' AND (status='F' OR status='P') AND customer_id='$customer_id'");
        $row = $result->fetch_assoc();
        return $row[$this->pk] * 1;
    }

    public function name($primary_id)
    {
        $result = $this->select($this->table, $this->name, "$this->pk = '$primary_id'");
        $row = $result->fetch_assoc();
        return $row[$this->name];
    }

    public function dataRow($primary_id, $field)
    {
        $result = $this->select($this->table, $field, "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_array();
            return $row[$field];
        } else {
            return "";
        }
    }

    public function detailsRow($primary_id, $field)
    {
        $result = $this->select($this->table_detail, $field, "$this->pk2 = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_array();
            return $row[$field];
        } else {
            return "";
        }
    }

    public function add_detail()
    {
        $form = array(
            $this->pk               => $this->inputs[$this->pk],
            $this->fk_det           => $this->inputs[$this->fk_det],
            // 'expense_category_id'   => $this->inputs['expense_category_id'],
            'expense_desc'          => $this->inputs['expense_desc'],
            'expense_amount'        => $this->inputs['expense_amount'],
        );
        return $this->insert($this->table_detail, $form);
    }

    public function show_detail()
    {
        $param = isset($this->inputs['param']) ? $this->inputs['param'] : null;
        $rows = array();
        $count = 1;
        $ChartOfAccounts = new ChartOfAccounts;
        $ExpenseCategory = new ExpenseCategory;
        $result = $this->select($this->table_detail, '*', $param);
        while ($row = $result->fetch_assoc()) {
            $row['chart'] = $ChartOfAccounts->name($row['chart_id']);
            // $row['category'] = $ExpenseCategory->name($row['expense_category_id']);
            $row['count'] = $count++;
            $rows[] = $row;
        }
        return $rows;
    }

    public function remove_detail()
    {
        $ids = implode(",", $this->inputs['ids']);
        return $this->delete($this->table_detail, "$this->pk2 IN($ids)");
    }

    public function generate()
    {
        return 'EXP-' . date('YmdHis');
    }

    public function total_per_chart($start_date, $end_date, $chart_id, $journal_id)
    {
        $result = $this->select("tbl_expenses as h, tbl_expense_details as d", 'sum(d.debit) as total_debit, sum(d.credit) as total_credit', "(h.journal_date >= '$start_date' AND h.journal_date <= '$end_date') AND h.expense_id=d.expense_id AND h.status='F' AND d.chart_id='$chart_id' AND h.journal_id='$journal_id'");

        $row = $result->fetch_assoc();
        return $row;
    }

    public function chart_per_year($year, $chart_id)
    {
        $result = $this->select("tbl_expenses as h, tbl_expense_details as d", 'sum(d.debit-d.credit) as total', "YEAR(journal_date)='$year' AND h.expense_id=d.expense_id AND h.status='F' AND d.chart_id='$chart_id'");

        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function journal_book()
    {
        $journal_id = $this->inputs['journal_id'];
        $start_date = $this->inputs['start_date'];
        $end_date = $this->inputs['end_date'];

        $rows = array();
        $Chart = new ChartOfAccounts;
        $result = $this->select($this->table, '*', "journal_id='$journal_id' AND (journal_date >= '$start_date' AND journal_date <= '$end_date')");
        while ($row = $result->fetch_assoc()) {
            $details = $this->select($this->table_detail, "*", "expense_id='$row[expense_id]' ORDER BY debit DESC");

            $chart_data = "";
            $debit_data = "";
            $credit_data = "";
            while ($dRow = $details->fetch_array()) {
                $type = $dRow['debit'] > 0 ? "" : "&emsp;&emsp;";
                $debit = $dRow['debit'] > 0 ? number_format($dRow['debit']) : "";
                $credit = $dRow['credit'] > 0 ? number_format($dRow['credit']) : "";
                $chart_data .= $type . $Chart->name($dRow['chart_id']) . "<br>";
                $debit_data .= $debit . "<br>";
                $credit_data .= $credit . "<br>";
            }
            $remarks = $row['remarks'] == "" ? "" : '<br>(' . $row['remarks'] . ')';

            $row['date'] = date('M d, Y', strtotime($row["journal_date"]));
            $row['general_reference'] = $row["reference_number"] . $remarks;
            $row['account'] = $chart_data;
            $row['debit'] = $debit_data;
            $row['credit'] = $credit_data;
            $rows[] = $row;
        }

        return $rows;
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
        $expenses_data = [];
        $count = 0;
        $success_import = 0;
        $unsuccess_import = 0;
        $ChartOfAccounts = new ChartOfAccounts();
        $Journals = new Journals();
        foreach ($csvData as $row) {
            if ($count > 0) {

                $journal_id = $Journals->idByName($row[3]);
                $credit_method = $ChartOfAccounts->idByName($row[4]);
                $chart_id = $ChartOfAccounts->idByName($row[6]);

                if ($credit_method > 0 && $journal_id > 0) {

                    $form = [
                        'reference_number' => $row[0],
                        'branch_id' => $row[1] ? $branches[$row[1]] : 1,
                        'branch_name' => $row[1] == 'BCD' ? "Bacolod" : "La Carlota",
                        'expense_date' => date("Y-m-d", strtotime($row[2])),
                        'journal_id' => $journal_id,
                        'journal_name' => $this->clean($row[3]),
                        'credit_method' => $credit_method,
                        'payment_method' => $this->clean($row[4]),
                        'remarks' => $this->clean($row[5]),
                        'status' => "F",
                        'chart_id' => "", //$chart_id,
                        'chart_name' => "", //$this->clean($row[6]),
                        'expense_desc' => "", //$this->clean($row[7]),
                        'expense_amount' => "", //(float) str_replace(',', '', $row[8]),
                    ];

                    $Expenses = new Expenses;
                    $Expenses->inputs = $form;
                    $expense_id = $row[0] != '' ? $Expenses->add() : 0;
                    if ($expense_id > 0) {
                        $form['import_status'] = 1;
                        $success_import += 1;
                    } else if ($expense_id == -2) {
                        $form['import_status'] = 0;
                        $unsuccess_import += 1;
                    } else {
                        $form['import_status'] = 0;
                        $unsuccess_import += 1;
                    }
                } else {
                    $expense_id = $this->idByName($row[0]);
                    $form = [
                        'reference_number' => $row[0],
                        'expense_id' => $expense_id,
                        'branch_id' => "",
                        'branch_name' => "",
                        'expense_date' => "",
                        'journal_id' => "",
                        'journal_name' => "",
                        'credit_method' => "",
                        'payment_method' => "",
                        'remarks' => "",
                        'chart_id' => $chart_id,
                        'chart_name' => $this->clean($row[6]),
                        'expense_desc' => $this->clean($row[7]),
                        'expense_amount' => (float) str_replace(',', '', $row[8]),
                    ];
                    if ($chart_id > 0 && $expense_id) {
                        $Expenses = new Expenses;
                        $Expenses->inputs = $form;
                        $expense_detail_status = $row[0] != '' ? $Expenses->add_detail() : 0;
                        if ($expense_detail_status > 0) {
                            $form['import_status'] = 1;
                            $success_import += 1;
                        } else {
                            $form['import_status'] = 0;
                            $unsuccess_import += 1;
                        }
                    } else {
                        $form['import_status'] = 0;
                        $unsuccess_import += 1;
                    }
                }

                $expenses_data[] = $form;
            }
            $count++;
        }
        $response['status'] = 1;
        $response['disbursements'] = $expenses_data;
        $response['success_import'] = $success_import;
        $response['unsuccess_import'] = $unsuccess_import;
        return $response;
    }

    public function schema()
    {
        $default['date_added'] = $this->metadata('date_added', 'datetime', '', 'NOT NULL', 'CURRENT_TIMESTAMP');
        $default['date_last_modified'] = $this->metadata('date_last_modified', 'datetime', '', 'NOT NULL', '', 'ON UPDATE CURRENT_TIMESTAMP');


        // TABLE HEADER
        $tables[] = array(
            'name'      => $this->table,
            'primary'   => $this->pk,
            'fields' => array(
                $this->metadata($this->pk, 'int', 11, 'NOT NULL', '', 'AUTO_INCREMENT'),
                $this->metadata($this->name, 'varchar', 150),
                $this->metadata('expense_date', 'date'),
                $this->metadata('remarks', 'varchar', 255),
                $this->metadata('journal_id', 'int', 11),
                $this->metadata('status', 'varchar', 1),
                $this->metadata('user_id', 'int', 11),
                $this->metadata('credit_method', 'int', 11),
                $this->metadata('branch_id', 'int', 11),
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
