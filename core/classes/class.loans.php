<?php

class Loans extends Connection
{
    private $table = 'tbl_loans';
    public $pk = 'loan_id';
    public $name = 'reference_number';

    public $inputs;


    public function add()
    {
        $LoanTypes = new LoanTypes;
        try {
            $this->checker();
            $this->begin_transaction();
            $form = array(
                $this->name             => $this->clean($this->inputs[$this->name]),
                'branch_id'             => $this->clean($this->inputs['branch_id']),
                'client_id'             => $this->clean($this->inputs['client_id']),
                'loan_type_id'          => $this->clean($this->inputs['loan_type_id']),
                'loan_date'             => $this->clean($this->inputs['loan_date']),
                'loan_amount'           => $this->clean($this->inputs['loan_amount']),
                'loan_period'           => $this->clean($this->inputs['loan_period']),
                'loan_interest'         => $this->clean($this->inputs['loan_interest']),
                'penalty_percentage'    => $this->clean($this->inputs['penalty_percentage']),
                'service_fee'           => $this->clean($this->inputs['service_fee']),
                'monthly_payment'       => $this->clean($this->inputs['monthly_payment']),
                'payment_terms'         => $this->clean($this->inputs['payment_terms']),
                'payment_date_start'    => $this->clean($this->inputs['payment_date_start']),
                'fixed_interest'        => $this->clean($LoanTypes->fixed_status($this->inputs['loan_type_id'])),
                'status'                => $this->inputs['status'] ?? 'A',
                'main_loan_id'          => $this->inputs['main_loan_id'] ?? 0,
                'renewal_status'        => $this->inputs['renewal_status'] ?? 'N',
                'deduct_to_loan'        => $this->inputs['deduct_to_loan'] ?? 0,
                'is_imported'           => $this->inputs['is_imported'] ?? 0,
            );

            $last_id = $this->inputs['last_id'] ?? "N";
            $is_success_or_id = $this->insertIfNotExist($this->table, $form, "$this->name = '" . $this->inputs[$this->name] . "'", $last_id);
            if ($is_success_or_id < 1)
                throw new Exception($is_success_or_id);
            $this->commit();
            Logs::action($this->action_response, "Loans", "Loans->add");
            return $is_success_or_id;
        } catch (Exception $e) {
            $this->rollback();
            Logs::error("Collections->add", "Collection", $e->getMessage());
            return $e->getMessage();
        }
    }

    public function renew()
    {

        $primary_id = $this->inputs[$this->pk];
        $row = $this->view($primary_id);
        $deduct_to_loan = (!isset($this->inputs['deduct_to_loan']) ? 0 : 1);
        $monthly_payment = $this->inputs['monthly_payment']; // - $row['monthly_payment'];
        $form = array(
            $this->name             => $this->clean($this->inputs[$this->name]),
            'branch_id'             => $row['branch_id'],
            'client_id'             => $row['client_id'],
            'loan_type_id'          => $row['loan_type_id'],
            'loan_date'             => $this->clean($this->inputs['loan_date']),
            'loan_amount'           => $this->clean($this->inputs['loan_amount']),
            'loan_period'           => $this->clean($this->inputs['loan_period']),
            'loan_interest'         => $this->clean($this->inputs['loan_interest']),
            'penalty_percentage'    => $this->clean($this->inputs['penalty_percentage']),
            'service_fee'           => $this->clean($this->inputs['service_fee']),
            'payment_date_start'    => $this->clean($this->inputs['payment_date_start']),
            'monthly_payment'       => $monthly_payment,
            'main_loan_id'          => $this->clean($this->inputs['loan_id']),
            'payment_terms'         => $this->clean($this->inputs['payment_terms']),
            'renewal_status'        => $this->clean($this->inputs['renewal_status']),
            'deduct_to_loan'        => $deduct_to_loan,
        );

        if (isset($this->inputs['status']))
            $form['status'] = $this->inputs['status'];

        $sql =  $this->insertIfNotExist($this->table, $form);
        Logs::action($this->action_response, "Loans", "Loans->renew");

        if ($this->inputs['renewal_status'] == "Y") {
            if ($deduct_to_loan != 1) {
                if ($sql) {
                    $Branches           = new Branches;
                    $ChartOfAccounts    = new ChartOfAccounts;
                    $Journals           = new Journals;
                    $LoanTypes          = new LoanTypes;

                    $jl             = $Journals->jl_data('Collection');
                    $ref_code       = $jl['journal_code'] . "-" . date('YmdHis');
                    $branch_name    = str_replace(" Branch", "", $Branches->name($this->clean($row['branch_id'])));

                    $loan_row               = $this->loan_data($this->inputs['loan_id']);
                    $amount                 = $this->clean($this->inputs['amount']);
                    $monthly_interest_rate  = ($loan_row['loan_interest'] / 100) / 12;
                    $total_interest         = ($loan_row['loan_amount'] * $monthly_interest_rate) * $loan_row['loan_period'];
                    $interest               = ($amount / ($loan_row['loan_amount'] + $total_interest)) * $total_interest;

                    $collection_num = 'CL-' . date('YmdHis');

                    $form_cl = array(
                        'reference_number'  => $collection_num,
                        'loan_id'           => $this->inputs['loan_id'],
                        'branch_id'         => $row['branch_id'],
                        'chart_id'          => $this->clean($this->inputs['chart_id']),
                        'client_id'         => $row['client_id'],
                        'interest'          => $interest,
                        'amount'            => $amount,
                        'collection_date'   => $this->clean($this->inputs['loan_date']),
                        'penalty_amount'    => $this->clean($this->inputs['penalty_amount']),
                        'remarks'           => "Renew Loan for " . $row['reference_number'],
                        'user_id'           => $this->clean($_SESSION['lms_user_id']),
                    );

                    $this->insert("tbl_collections", $form_cl, "Y");


                    $form_journal = array(
                        'reference_number'  => $ref_code,
                        'branch_id'         => $row['branch_id'],
                        'cross_reference'   => $collection_num,
                        'journal_id'        => $jl['journal_id'],
                        'remarks'           => "Renew loan (Loan ID: " . $this->clean($this->inputs[$this->name]),
                        'journal_date'      => $this->inputs['loan_date'],
                        'status'            => 'F',
                        'user_id'           => $_SESSION['lms_user_id'],
                        'is_manual'         => 'N'
                    );

                    $journal_entry_id = $this->insert("tbl_journal_entries", $form_journal, 'Y');

                    // FOR CASH IN BANK
                    $cnb_total = $amount + $this->inputs['penalty_amount'];
                    $form_cnb = array('journal_entry_id' => $journal_entry_id, 'chart_id' => $this->clean($this->inputs['chart_id']), 'debit' => $cnb_total);
                    $this->insert('tbl_journal_entry_details', $form_cnb);


                    // FOR INTEREST
                    $int_chart = $ChartOfAccounts->chart_data('Interest Income - ' . $branch_name);
                    $form_interest = array('journal_entry_id' => $journal_entry_id, 'chart_id' => $int_chart['chart_id'], 'credit' => $interest);
                    $this->insert('tbl_journal_entry_details', $form_interest);


                    // FOR PENALTY
                    if ($this->inputs['penalty_amount'] > 0) {

                        $penalty_chart = $ChartOfAccounts->chart_data('Penalty Income - ' . $branch_name);
                        $form_penalty = array('journal_entry_id' => $journal_entry_id, 'chart_id' => $penalty_chart['chart_id'], 'credit' => $this->inputs['penalty_amount']);
                        $this->insert('tbl_journal_entry_details', $form_penalty);
                    }


                    // FOR LOANS RECEIVABLE
                    $lr_total = $cnb_total - ($this->inputs['penalty_amount'] + $interest);
                    $loan_type = $LoanTypes->name($row['loan_type_id']);
                    $lr_chart = $ChartOfAccounts->chart_data('Loans Receivable - ' . $loan_type . " - " . $branch_name);
                    $form_penalty = array('journal_entry_id' => $journal_entry_id, 'chart_id' => $lr_chart['chart_id'], 'credit' => $lr_total);
                    $this->insert('tbl_journal_entry_details', $form_penalty);

                    if ($this->loan_balance($this->inputs['loan_id'], $this->inputs['loan_date']) <= 0) {
                        $form_finished = array(
                            'status' => 'F'
                        );
                        $this->update("tbl_loans", $form_finished, 'loan_id="' . $this->inputs['loan_id'] . '"');
                    }
                }
            }
            Logs::action("Sucessfully renewed loan (" . $this->name($this->inputs['loan_id']) . " -> " . $this->clean($this->inputs[$this->name]) . ")", "Loans", "Loans->renew");
        }

        $bal = $this->loan_balance($this->inputs['loan_id'], $this->inputs['loan_date']);
        return $sql;
    }

    public function edit()
    {
        $primary_id = $this->inputs[$this->pk];
        $name = $this->clean($this->inputs['reference_number']);
        $is_exist = $this->select($this->table, $this->pk, "$this->name = '" . $this->inputs[$this->name] . "' AND $this->pk != '$primary_id'");
        if ($is_exist->num_rows > 0) {
            return 2;
        } else {
            $form = array(
                $this->name             => $this->clean($this->inputs[$this->name]),
                'branch_id'             => $this->clean($this->inputs['branch_id']),
                'client_id'             => $this->clean($this->inputs['client_id']),
                'loan_type_id'          => $this->clean($this->inputs['loan_type_id']),
                'loan_date'             => $this->clean($this->inputs['loan_date']),
                'loan_amount'           => $this->clean($this->inputs['loan_amount']),
                'loan_period'           => $this->clean($this->inputs['loan_period']),
                'loan_interest'         => $this->clean($this->inputs['loan_interest']),
                'penalty_percentage'    => $this->clean($this->inputs['penalty_percentage']),
                'service_fee'           => $this->clean($this->inputs['service_fee']),
                'monthly_payment'       => $this->clean($this->inputs['monthly_payment']),
                'payment_terms'         => $this->clean($this->inputs['payment_terms']),
                'payment_date_start'    => $this->clean($this->inputs['payment_date_start']),
            );

            return $this->updateIfNotExist($this->table, $form, "$this->pk = '$primary_id'");
        }
    }

    public function show()
    {
        $param = isset($this->inputs['param']) ? $this->inputs['param'] : null;
        $rows = array();
        $Clients = new Clients;
        $LoanTypes = new LoanTypes;
        $result = $this->select($this->table, '*', $param);
        if ($result->num_rows < 1)
            return [];

        while ($row = $result->fetch_assoc()) {
            $row['client'] = $Clients->name($row['client_id']);
            $row['loan_account'] = $Clients->name($row['client_id']) . " - " . $row['reference_number'];
            $row['loan_type'] = $LoanTypes->name($row['loan_type_id']);
            $row['loan_ref_id'] = $row['reference_number'] . " (₱" . number_format($row['loan_amount'], 2) . ")";
            $row['loan_amount'] = "₱" . number_format($row['loan_amount'], 2);
            $rows[] = $row;
        }
        return $rows;
    }

    public function view($primary_id = null)
    {
        $primary_id = $primary_id == null ? $this->inputs['id'] : $primary_id;
        $LoanTypes = new LoanTypes;
        $Clients = new Clients;
        $Branches = new Branches;
        $result = $this->select($this->table, "*", "$this->pk = '$primary_id'");

        if ($result->num_rows < 1)
            return [
                'amount' => 0, 'branch_id' => 0, 'branch_name' => "", 'client' => "", 'client_id' => 0, 'date_added' => "", 'date_last_modified' => "", 'deduct_to_loan' => 0, 'due_date' => "", 'loan_amount' => 0, 'loan_date' => "", 'loan_id' => 0, 'loan_interest' => 0, 'loan_period' => 0, 'loan_type' => "", 'loan_type_id' => 0, 'main_loan_id' => 0, 'monthly_payment' => 0, 'payment_terms' => 0, 'penalty_percentage' => 0, 'reference_number' => "", 'renewal_status' => "", 'service_fee' => 0, 'status' => "", 'user_id' => 0
            ];

        $row = $result->fetch_assoc();
        $row['loan_type'] = $LoanTypes->name($row['loan_type_id']);
        $row['amount'] = number_format($row['loan_amount'], 2);
        $row['monthly_payment'] = $row['monthly_payment']; //number_format($row['monthly_payment'], 2);
        $row['monthly_payment_amount'] = number_format($row['monthly_payment'], 2);
        $row['client'] = $Clients->name($row['client_id']);
        $row['branch_name'] = $Branches->name($row['branch_id']);
        $row['loan_type'] = $LoanTypes->name($row['branch_id']);
        $row['renew_loan_date'] = $this->getCurrentDate();
        return $row;
    }

    public function finish()
    {
        $loan_id = $this->inputs['loan_id'] ?? 0;
        return $this->update($this->table, ['status' => 'F'], "loan_id = '$loan_id'");
    }

    public function client_id()
    {
        $primary_id = $this->inputs['id'];
        $result = $this->select($this->table, "client_id", "$this->pk = '$primary_id'");
        $row = $result->fetch_assoc();
        return $row['client_id'];
    }

    public function remove()
    {
        $ids = implode(",", $this->inputs['ids']);

        return $this->delete($this->table, "$this->pk IN($ids)");
    }

    public function released()
    {
        $primary_id = $this->inputs['id'];
        $form = array(
            'status' => 'R',
        );
        return $this->update($this->table, $form, "$this->pk = '$primary_id'");
    }

    public function denied()
    {
        $primary_id = $this->inputs['id'];
        $form = array(
            'status' => 'D',
        );
        return $this->update($this->table, $form, "$this->pk = '$primary_id'");
    }

    public function name($primary_id)
    {
        $result = $this->select($this->table, 'reference_number', "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['reference_number'];
        } else {
            return null;
        }
    }

    public function loan_client($primary_id)
    {
        $result = $this->select($this->table, 'client_id', "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['client_id'];
        } else {
            return null;
        }
    }

    public function generate()
    {
        return 'LN-' . date('YmdHis');
    }

    public function data_row($primary_id, $field)
    {
        $result = $this->select($this->table, $field, "$this->pk = '$primary_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_array();
            return $row[$field];
        } else {
            return "";
        }
    }


    public function pk_by_name($name = null)
    {
        $name = $name == null ? $this->inputs[$this->name] : $name;
        $result = $this->select($this->table, $this->pk, "$this->name = '$name'");
        $row = $result->fetch_assoc();
        return $row[$this->pk] * 1;
    }

    public function total($primary_id)
    {
        $result = $this->select($this->table, 'sum(loan_amount) as total', "$this->pk = '$primary_id'");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function loan_data($primary_id)
    {
        $result = $this->select($this->table, '*', "$this->pk = '$primary_id'");
        if ($result->num_rows < 1)
            return null;
        return $result->fetch_assoc();
    }

    public function sample_calculation()
    {
        $loan_interest = isset($this->inputs['loan_interest']) ? $this->inputs['loan_interest'] : null;
        $loan_period = isset($this->inputs['loan_period']) ? $this->inputs['loan_period'] : null;
        $loan_amount = isset($this->inputs['loan_amount']) ? $this->inputs['loan_amount'] : null;
        $loan_date = isset($this->inputs['loan_date']) ? $this->inputs['loan_date'] : null;
        $monthlypayment = isset($this->inputs['monthly_payment']) ? $this->inputs['monthly_payment'] : null;
        $payment_terms = isset($this->inputs['payment_terms']) ? $this->inputs['payment_terms'] : null;
        $loan_fixed_interest = isset($this->inputs['loan_fixed_interest']) ? $this->inputs['loan_fixed_interest'] : null;
        $payment_date_start = isset($this->inputs['payment_date_start']) ? $this->inputs['payment_date_start'] : null;

        $count = 1;
        $rows = array();
        $balance = $loan_amount;
        $payment_count = 1;
        $Collection = new Collections;
        while ($count <= $loan_period) {

            // $loan_date = date('M d, Y', strtotime('+1 month', strtotime($loan_date)));
            $payment_start = date('F Y', strtotime($payment_date_start));
            $loan_date = date('F Y', strtotime("first day of next month", strtotime($loan_date)));
            if ($loan_fixed_interest == 1) {
                $total_amount_with_interest = ($loan_interest * $loan_period) + $loan_amount;
                $monthly_interest = $loan_interest;
            } else {
                $monthly_interest_rate = ($loan_interest / 100) / 12;
                $total_amount_with_interest = ($loan_amount * $monthly_interest_rate * $loan_period) + $loan_amount;
                $monthly_interest = $balance * $monthly_interest_rate;
            }

            $suggested_payment = $loan_period > 0 ? $total_amount_with_interest / $loan_period : "";

            if ($payment_start == $loan_date or $payment_count == $payment_terms) { // matches every iterations
                $payment_count = 1;
                $monthly_payment = $monthlypayment > 0 ? $monthlypayment : ($suggested_payment * $payment_terms);
            } else {
                $payment_count += 1;
                $monthly_payment = 0;
            }
            // $penalty = $Collection->penalty_per_month($loan_date, $row['loan_id']);
            $principal_amount = $monthly_payment - $monthly_interest;
            $balance -= $principal_amount;

            $row['date'] = $loan_date;
            $row['payment'] = number_format($monthly_payment, 2); //number_format($suggested_payment, 2);
            $row['interest'] = number_format($monthly_interest, 2);
            $row['applicable_principal'] =  number_format($principal_amount, 2);
            $row['balance'] = number_format($balance, 2); //$balance > 0 ? number_format($balance, 2) : "0.00";
            $rows[] = $row;

            $count++;
        }

        return $rows;
    }

    public function statement_of_accounts()
    {
        $param = isset($this->inputs['param']) ? $this->inputs['param'] : null;
        $result = $this->select($this->table, '*', "$param");

        $rows = array();
        $bal = 0;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $loan_interest = $row['loan_interest'];
            $loan_period = $row['loan_period'];
            $loan_amount = $row['loan_amount'];
            $loan_date = $row['loan_date'];
            $payment_terms = $row['payment_terms'];
            $loan_type_id = $row['loan_type_id'];
            $payment_date_start = $row['payment_date_start'];
            $LoanTypes = new LoanTypes;
            $Collection = new Collections;
            $lt_row = $LoanTypes->view($loan_type_id);

            $loandate = date('Y-m-d', strtotime('+' . $loan_period . ' month', strtotime($payment_date_start)));
            $col_checker_date = $Collection->late_collection_checker($row['loan_id'], $loandate);

            if ($col_checker_date != 0) {
                $ts1 = strtotime($loandate);
                $ts2 = strtotime($col_checker_date);

                $year1 = date('Y', $ts1);
                $year2 = date('Y', $ts2);

                $month1 = date('m', $ts1);
                $month2 = date('m', $ts2);

                $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
                $late_col = $diff;
            } else {
                $late_col = 0;
            }


            $count = 1;

            $monthly_interest_rate = ($loan_interest / 100) / 12;
            $total_amount_with_interest = ($loan_amount * $monthly_interest_rate * $loan_period) + $loan_amount;

            $balance = $loan_amount;
            $payment_count = 1;
            while ($count <= ($loan_period + $late_col)) {

                $loan_date = date('Y-m-d', strtotime("first day of next month", strtotime($loan_date)));

                if ($payment_count == $payment_terms or $payment_date_start == $loan_date) { // matches every 3 iterations
                    $payment_count = 1;
                    $payment = $row['monthly_payment'];
                } else {
                    $payment_count += 1;
                    $payment = 0;
                }

                if ($lt_row['fixed_interest'] == "Y") {
                    $monthly_interest = $loan_interest;
                } else {
                    $monthly_interest_rate = ($loan_interest / 100) / 12;
                    $monthly_interest = $balance * $monthly_interest_rate;
                }
                $principal_amount = $payment - $monthly_interest; //$balance / $loan_period;

                $penalty = $Collection->penalty_per_month($loan_date, $row['loan_id']);
                // $payment = $count == 1 ? $Collection->collected_per_month($loan_date,$row['loan_id'])+$Collection->advance_collection($row['loan_id']) : $Collection->collected_per_month($loan_date,$row['loan_id']);
                $balance -= ($principal_amount - $penalty); //($payment + $penalty);


                $row['date'] = date('F Y', strtotime($loan_date));
                $row['payment'] = number_format($payment, 2);
                $row['interest'] = number_format($monthly_interest, 2);
                $row['penalty'] = number_format($penalty, 2);
                $row['applicable_principal'] =  number_format($principal_amount, 2);
                $row['balance'] = $balance > 0 ? number_format($balance, 2) : "0.00";
                $rows[] = $row;

                $count++;
            }

            return $rows;
        } else {
            return $rows;
        }
    }

    public function soa_collection()
    {
        $param = isset($this->inputs['param']) ? $this->inputs['param'] : null;
        $result = $this->select($this->table, '*', "$param");

        $rows = array();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $loan_interest = $row['loan_interest'];
            $loan_period = $row['loan_period'];
            $loan_amount = $row['loan_amount'];
            $loan_date = $row['loan_date'];
            $payment_terms = $row['payment_terms'];
            $LoanTypes = new LoanTypes;
            $lt_row = $LoanTypes->view($row['loan_type_id']);

            $count = 1;

            $balance = $loan_amount;
            $Collection = new Collections;

            $loandate = date('Y-m-d', strtotime('+' . $loan_period . ' month', strtotime($loan_date)));
            $col_checker_date = $Collection->late_collection_checker($row['loan_id'], $loandate);

            if ($col_checker_date != 0) {
                $ts1 = strtotime($loandate);
                $ts2 = strtotime($col_checker_date);

                $year1 = date('Y', $ts1);
                $year2 = date('Y', $ts2);

                $month1 = date('m', $ts1);
                $month2 = date('m', $ts2);

                $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
                $late_col = $diff;
            } else {
                $late_col = 0;
            }

            $payment_count = 1;
            while ($count <= ($loan_period + $late_col)) {

                $loan_date = date('Y-m-d', strtotime("first day of next month", strtotime($loan_date)));

                if ($payment_count == $payment_terms) { // matches every 3 iterations
                    $payment_count = 1;
                    $payment = $count == 1 ? $Collection->collected_per_month($loan_date, $row['loan_id']) + $Collection->advance_collection($row['loan_id']) : $Collection->collected_per_month($loan_date, $row['loan_id']);
                } else {
                    $payment_count += 1;
                    $payment = 0;
                }


                // $suggested_payment = $loan_period > 0 ? $total_amount_with_interest / $loan_period : "";

                if ($lt_row['fixed_interest'] == "Y") {
                    $monthly_interest = $loan_interest;
                } else {
                    $monthly_interest_rate = ($loan_interest / 100) / 12;
                    $monthly_interest = $balance * $monthly_interest_rate;
                }

                // $monthly_interest = $balance * $monthly_interest_rate;
                $principal_amount = $payment - $monthly_interest;
                $penalty = $Collection->penalty_per_month($loan_date, $row['loan_id']);

                $addCounter = $this->select($this->table, 'count(loan_id) as counter,renewal_status', "main_loan_id='$row[loan_id]'"); // AND renewal_status = 'N'
                $alRow = $addCounter->fetch_assoc();

                // if ($alRow['counter'] > 0) {
                //     $th_additional = $alRow['renewal_status'] == "N" ? '<th style="color:#fff;">ADDITIONAL LOAN</th>' : "";
                //     $th_renewal = $alRow['renewal_status'] == "Y" ? '<th style="color:#fff;"></th>' : "";
                //     $colspan = 6;
                // } else {
                //     $th_additional = "";
                //     $th_renewal = "";
                //     $colspan = 5;
                // }
                $year = date('Y', strtotime($loan_date));
                $month = date('m', strtotime($loan_date));

                if ($alRow['counter'] > 0) {
                    $additional_amount = $this->additional_loan($month, $year, $row['loan_id']);
                    $renewal_status = $additional_amount > 0 ? "<strong style='color:red;'>RENEWAL</strong>" : "";
                    $td_add = $alRow['renewal_status'] == "N" ? $additional_amount : "";
                    $td_renewal = $alRow['renewal_status'] == "Y" ? $renewal_status : "";
                    $balance += (($additional_amount - $principal_amount) - $penalty);
                    $td_colspan = "3";
                } else {
                    $td_add = "";
                    $td_renewal = "";
                    $additional_amount = 0;
                    $balance -= ($principal_amount - $penalty);
                    $td_colspan = "2";
                }


                $row['date'] = date('F Y', strtotime($loan_date));
                $row['payment'] = number_format($payment, 2);
                $row['interest'] = number_format($monthly_interest, 2);
                $row['penalty'] = number_format($penalty, 2);
                $row['applicable_principal'] =  $principal_amount > 0 ? number_format($principal_amount, 2) : "0.00";
                $row['balance'] = $balance > 0 ? number_format($balance, 2) : "0.00";
                $rows[] = $row;

                $count++;
            }

            return $rows;
        } else {
            return $rows;
        }
    }

    public function accounts_receivable()
    {
        $report_year = $this->inputs['report_year'];
        $rows = array();
        $Clients = new Clients;
        $Collections = new Collections;
        $result = $this->select($this->table, '*', "YEAR(loan_date) = '$report_year' AND status != 'D'");
        while ($row = $result->fetch_assoc()) {
            $colRow = $Collections->total_collected_by_loan($row['loan_id']);
            $payment = $colRow[0];
            $amount_receivable = $row['loan_amount'] - $payment;
            $penalty = $colRow[1];
            $subtotal = $amount_receivable + $penalty;
            $row['client'] = $Clients->name($row['client_id']);
            $row['amount'] = number_format($row['loan_amount'], 2);
            $row['total_payment'] = number_format($payment, 2);
            $row['amount_receivable'] = number_format($amount_receivable, 2);
            $row['total_penalties'] = number_format($penalty, 2);
            $row['subtotal'] = number_format($subtotal, 2);
            $rows[] = $row;
        }
        return $rows;
    }

    public function released_total()
    {
        $year = date('Y');
        $result = $this->select($this->table, "sum(loan_amount) as total", "YEAR(loan_date) = '$year' AND (status = 'R' OR status='F')");
        $row = $result->fetch_assoc();
        return $row['total'];
    }


    public function outstanding_total()
    {
        $total = 0;
        $result = $this->select($this->table, "loan_id,loan_amount,client_id", "status = 'R'");
        while ($row = $result->fetch_assoc()) {
            $collection = $this->select('tbl_collections', "sum(amount) as total", "status = 'F' AND loan_id='$row[loan_id]' AND client_id='$row[client_id]'");
            $total_collected = $collection->fetch_assoc();

            $total += $row['loan_amount'] - $total_collected['total'];
        }

        return $total;
    }

    public function approved_loans()
    {
        $year = date('Y');
        $result = $this->select($this->table, "count(loan_id) as total", "YEAR(loan_date) = '$year' AND (status = 'R' OR status = 'A' OR status='F')");
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function pending_loans()
    {
        $year = date('Y');
        $result = $this->select($this->table, "count(loan_id) as total", "YEAR(loan_date) = '$year' AND status = 'A'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['total'];
        } else {
            return null;
        }
    }

    public function dashboard()
    {
        $rows = array();
        $row['outstanding_total'] = number_format($this->outstanding_total(), 2);
        $row['released_total'] = number_format($this->released_total(), 2);
        $row['approved_total'] = $this->approved_loans();
        $row['pending_total'] = $this->pending_loans();
        $rows = $row;
        return $rows;
    }

    public function penalty()
    {
        $loan_id = $this->inputs['loan_id']; //isset($this->inputs['loan_id']) ? $this->inputs['loan_id'] : null;
        $collection_date = $this->inputs['collection_date'];

        $result = $this->select($this->table, '*', "loan_id='$loan_id'");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            $loan_interest = $row['loan_interest'];
            $loan_period = $row['loan_period'];
            $loan_amount = $row['loan_amount'];
            $monthly_payment = $row['monthly_payment'];
            $loan_date = $row['loan_date'];
            $penalty_per = $row['penalty_percentage'];
            $payment_terms = $row['payment_terms'];
        } else {
            $loan_interest = "";
            $loan_period = "";
            $loan_amount = "";
            $loan_date = "";
            $penalty_per = 0;
            $payment_terms = "";
        }

        $ts1 = strtotime($loan_date);
        $ts2 = strtotime($collection_date);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        $diff = (($year2 - $year1) * 12) + ($month2 - $month1);

        $suggested_total = 0;
        $payment_total = 0;
        $count = 2;
        $payment_count = 1;
        $Collection = new Collections;
        while ($count <= $diff) {
            $loan_date = date('Y-m-d', strtotime("first day of next month", strtotime($loan_date)));
            $counter_date = date('Y-m-d', strtotime('-' . $payment_count . ' month', strtotime($loan_date)));

            if ($payment_terms > 1) {
                if ($payment_count == $payment_terms) { // matches every terms iterations
                    $payment_count = 1;
                    $payment = $Collection->collected_per_month($loan_date, $loan_id);
                    $suggested_total += $monthly_payment;
                    $payment_total += $payment;
                } else if ($Collection->penalty_checker($counter_date, $loan_id) <= 0) {
                    $payment_count = 2;
                    $payment = $Collection->collected_per_month($loan_date, $loan_id);
                    $suggested_total += $monthly_payment;
                    $payment_total += $payment;
                } else {
                    $payment_count += 1;
                    $payment = 0;
                    $suggested_total = 0;
                    $payment_total = 0;
                }
            } else {
                $payment_count = 1;
                $payment = $Collection->collected_per_month($loan_date, $loan_id);
                $suggested_total += $monthly_payment;
                $payment_total += $payment;
            }
            $count++;
        }

        $pending = $suggested_total - $payment_total;
        $total_penalty = $pending * ($penalty_per / 100);

        return $total_penalty < 0 ? 0 : $total_penalty;

        // return $suggested_total." ".$payment_total;
    }

    public function total_loan($loan_id)
    {

        $result = $this->select($this->table, 'loan_amount,loan_interest,loan_period', "loan_id='$loan_id'");
        $row = $result->fetch_assoc();

        $loan_interest = $row['loan_interest'];
        $loan_period = $row['loan_period'];
        $loan_amount = $row['loan_amount'];

        $monthly_interest_rate = ($loan_interest / 100) / 12;
        $total_amount_with_interest = ($loan_amount * $monthly_interest_rate * $loan_period) + $loan_amount;

        return $total_amount_with_interest;
    }


    public function additional_loan($month, $year, $loan_id)
    {
        $result = $this->select($this->table, 'sum(loan_amount) as total', "(MONTH(loan_date) = '$month' AND YEAR(loan_date)= '$year') AND main_loan_id='$loan_id'"); // AND renewal_status='N'
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    public function loan_balance($primary_id = null, $date = null)
    {
        $primary_id = $primary_id != "" ? $primary_id : $this->inputs[$this->pk];
        $date = $date != "" ? $date : $this->inputs['loan_date'];

        // $Collections = new Collections;

        // $bal = $this->total_loan($primary_id)-$Collections->total_collected($primary_id);
        // return $bal;

        $result = $this->select($this->table, '*', "loan_id='$primary_id'");

        $row = $result->fetch_assoc();

        $loan_interest = $row['loan_interest'];
        $loan_period = $row['loan_period'];
        $loan_amount = $row['loan_amount'];
        $loan_date = $row['loan_date'];
        $payment_terms = $row['payment_terms'];
        $loan_type_id = $row['loan_type_id'];
        $LoanTypes = new LoanTypes;
        $Collection = new Collections;
        $lt_row = $LoanTypes->view($loan_type_id);

        $loandate = date('Y-m-d', strtotime('+' . $loan_period . ' month', strtotime($loan_date)));
        $col_checker_date = $Collection->late_collection_checker($row['loan_id'], $loandate);

        if ($col_checker_date != 0) {
            $ts1 = strtotime($loandate);
            $ts2 = strtotime($col_checker_date);

            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);

            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);

            $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
            $late_col = $diff;
        } else {
            $late_col = 0;
        }

        $ldate = strtotime($loan_date);
        $cdate = strtotime($date);

        $year_1 = date('Y', $ldate);
        $year_2 = date('Y', $cdate);

        $month_1 = date('m', $ldate);
        $month_2 = date('m', $cdate);

        $l_period = ((($year_2 - $year_1) * 12) + ($month_2 - $month_1));


        $count = 1;

        $monthly_interest_rate = ($loan_interest / 100) / 12;

        $balance = $loan_amount;
        $payment_count = 1;
        while ($count <= ($l_period + $late_col)) {

            $loan_date = date('Y-m-d', strtotime("first day of next month", strtotime($loan_date)));

            if ($payment_count == $payment_terms) { // matches every 3 iterations
                $payment_count = 1;
                $payment = $count == 1 ? $Collection->collected_per_month($loan_date, $row['loan_id']) + $Collection->advance_collection($row['loan_id']) : $Collection->collected_per_month($loan_date, $row['loan_id']);
            } else {
                $payment_count += 1;
                $payment = 0;
            }

            if ($lt_row['fixed_interest'] == "Y") {
                $monthly_interest = $loan_interest;
            } else {
                $monthly_interest_rate = ($loan_interest / 100) / 12;
                $monthly_interest = $balance * $monthly_interest_rate;
            }

            $addCounter = $this->select($this->table, 'count(loan_id) as counter,renewal_status', "main_loan_id='$row[loan_id]'"); // AND renewal_status = 'N'
            $alRow = $addCounter->fetch_assoc();

            $year = date('Y', strtotime($loan_date));
            $month = date('m', strtotime($loan_date));

            $principal_amount = $payment - $monthly_interest; //$payment > 0 ? $payment - $monthly_interest : 0;
            $penalty = $Collection->penalty_per_month($loan_date, $row['loan_id']);
            if ($alRow['counter'] > 0) {
                $additional_amount = $this->additional_loan($month, $year, $row['loan_id']);
                $balance += (($additional_amount - $principal_amount) - $penalty);
            } else {
                $additional_amount = 0;
                $balance -= ($principal_amount - $penalty);
            }

            $balance -= ($principal_amount - $penalty);
            $count++;
        }

        return round($balance, 2);
    }


    public function idByName($reference_number)
    {
        $result = $this->select($this->table, 'loan_id', "reference_number = '$reference_number'");

        if ($result->num_rows < 1)
            return 0;

        $row = $result->fetch_assoc();
        return $row['loan_id'];
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
                $this->metadata('branch_id', 'int', 11),
                $this->metadata('client_id', 'int', 11),
                $this->metadata('loan_type_id', 'int', 11),
                $this->metadata('loan_amount', 'decimal', '12,4'),
                $this->metadata('loan_interest', 'decimal', '12,4'),
                $this->metadata('loan_period', 'int', 4),
                $this->metadata('penalty_percentage', 'decimal', '12,4'),
                $this->metadata('payment_terms', 'decimal', '4,2'),
                $this->metadata('due_date', 'date'),
                $this->metadata('status', 'varchar', 1, 'NOT NULL', "'A'", '', "'P - Pending; A - Approved; R - Released; D - Denied; F- Fully paid'"),
                $this->metadata('renewal_status', 'varchar', 1),
                $this->metadata('loan_date', 'date', '', 'NULL'),
                $this->metadata('payment_date_start', 'date', '', 'NULL'),
                $this->metadata('service_fee', 'decimal', '12,3'),
                $this->metadata('monthly_payment', 'decimal', '12,4'),
                $this->metadata('main_loan_id', 'int', 11),
                $this->metadata('deduct_to_loan', 'int', '1'),
                $this->metadata('is_imported', 'int', '1'),
                $this->metadata('fixed_interest', 'varchar', '1'),
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
