<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LoanUploads extends Connection
{
    private $table = 'tbl_loans';
    public $pk = 'loan_id';
    public $name = 'reference_number';

    public $inputs;

    public function import()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $response = [];
        $file = $_FILES['csv_file'];
        $fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($fileType != 'csv' || $fileType != 'xls') {
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
        $loans_data = [];
        $count = 0;
        $success_import = 0;
        $unsuccess_import = 0;

        $Clients = new Clients();
        $LoanTypes = new LoanTypes();

        foreach ($csvData as $row) {
            if ($count > 0) {
                $client_id = $Clients->idByFullname($this->clean($row[2]));
                $loan_type_id = $LoanTypes->idByName($this->clean($row[3]));
                $branch_name = $row[0] == 'BCD' ? "Bacolod" : "La Carlota";
                $form = [
                    'branch_id' => $row[0] ? $branches[$row[0]] : 1,
                    'branch_name' => $branch_name,
                    'reference_number' => $row[1],
                    'client_id' => $client_id,
                    'client_name' => $this->clean($row[2]),
                    'loan_type_id' => $loan_type_id,
                    'loan_type' => $this->clean($row[3]),
                    'loan_date' => date("Y-m-d", strtotime($row[4])),
                    'loan_amount' => (float) str_replace(',', '', $row[5]),
                    'loan_interest' => (float) str_replace(',', '', $row[6]),
                    'loan_period' => (float) str_replace(',', '', $row[7]),
                    'service_fee' => (float) str_replace(',', '', $row[8]),
                    'monthly_payment' => (float) str_replace(',', '', $row[9]),
                    'status' => "R",
                ];

                if ($client_id > 0 && $loan_type_id > 0) {
                    $Loans = new Loans;
                    $Loans->inputs = $form;
                    $loan_id = $row[0] != '' ? $Loans->add() : 0;
                    if ($loan_id == 2) {
                        $form['import_status'] = 0;
                        $unsuccess_import += 1;
                    } else if ($loan_id == 0) {
                        $form['import_status'] = 0;
                        $unsuccess_import += 1;
                    } else {
                        $form['import_status'] = 1;
                        $success_import += 1;
                    }
                } else {
                    $form['import_status'] = 0;
                    $unsuccess_import += 1;
                }

                $loans_data[] = $form;
            }
            $count++;
        }
        $response['status'] = 1;
        $response['loans'] = $loans_data;
        $response['success_import'] = $success_import;
        $response['unsuccess_import'] = $unsuccess_import;
        return $response;
    }

    public function upload()
    {
        try {
            ini_set('memory_limit', '-1');
            ini_set('max_execution_time', 0);

            require_once '../vendor/autoload.php';

            $uploadedFile = $_FILES['excel_file']['tmp_name'];
            $spreadsheet = IOFactory::load($uploadedFile);
            $sheetNames = $spreadsheet->getSheetNames();

            $Clients = new Clients();
            $LoanTypes = new LoanTypes();

            $data = [];

            foreach ($sheetNames as $sheetName) {
                $worksheet = $spreadsheet->getSheetByName($sheetName);
                $client_name = $worksheet->getCell('A6')->getValue();
                $is_template = $worksheet->getCell('A5')->getValue();
                if ($is_template != 'STATEMENT OF ACCOUNT/LEDGER')
                    throw new Exception("INVALID - " . $sheetName);
                $client_id = $Clients->idByFullname($this->clean($client_name));
                $data[] = $this->worksheetLoans($worksheet, $client_id, $client_name);
            }
            $response['sheets'] = $data;
            $response['loan_types'] = $LoanTypes->show();
            $response['clients'] = $Clients->showFullnames();
            return [
                'status' => 'success',
                'data' => $response
            ];
        } catch (Exception $e) {
            Logs::error("Loans->upload", "Loans", $e->getMessage());
            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    public function worksheetLoans($worksheet, $client_id, $client_name)
    {
        $Clients = new Clients();
        $rowCount = $worksheet->getHighestRow();
        $loans = [];
        for ($row = 9; $row < $rowCount; $row++) {
            $loan_name = $worksheet->getCell('B' . $row)->getValue();
            if ($loan_name == 'PREPARED BY:')
                break;

            if ($loan_name != '')
                $loans[] = $this->worksheetLoanDetails($worksheet, $client_id, $loan_name, $row, $rowCount);
        }
        return [
            'client_id'     => (int) $client_id,
            'branch_id'     => $client_id > 0 ? (int) $Clients->getBranch($client_id) : 0,
            'client_name'   => $client_name,
            'loans'         => $loans
        ];
    }

    public function worksheetLoanDetails($worksheet, $client_id, $loan_name, $loan_row, $rowCount)
    {
        $LoanTypes      = new LoanTypes();
        $loan_type_id   = $LoanTypes->idByName($loan_name);

        $amount_row             = $loan_row - 7;
        $voucher_date_row       = $loan_row - 6;
        $term_row               = $loan_row - 5;
        $payment_term_row       = $loan_row - 4;
        $penalty_interest_row   = $loan_row - 3;
        $interest_row           = $loan_row - 2;
        $payment_row            = $loan_row + 8;
        $monthly_row            = $loan_row + 5;

        $loan_amount    = $worksheet->getCell('J' . $amount_row)->getValue();
        $cellDateValue  = (float) $worksheet->getCell('J' . $voucher_date_row)->getValue();
        $loan_date      = $cellDateValue > 0 ? Date::excelToDateTimeObject($cellDateValue)->format('m/d/Y') : '';

        $loan_period        = $worksheet->getCell('J' . $term_row)->getValue();
        $loan_period        = str_replace('MOS', '', $loan_period);
        $payment_terms      = (float) $worksheet->getCell('J' . $payment_term_row)->getValue();
        $penalty_percentage = ((float) $worksheet->getCell('J' . $penalty_interest_row)->getValue()) * 100;
        $loan_interest      = $worksheet->getCell('J' . $interest_row)->getValue();
        $loan_interest      = $loan_interest < 1 ? $loan_interest * 100 : $loan_interest;
        $monthly_payment    = $worksheet->getCell('D' . $monthly_row)->getValue();

        $collections = [];
        $balance = 0;

        $payment_date_start = '';
        $payment_day = date('d', strtotime($loan_date));
        for ($row = $payment_row; $row < $rowCount; $row++) {
            $payment_month = $worksheet->getCell('C' . $row)->getValue();
            if ($payment_month != '') {
                $payment_amount = (float) $worksheet->getCell('D' . $row)->getCalculatedValue();
                $interest       = (float) $worksheet->getCell('E' . $row)->getCalculatedValue();
                $penalty        = (float) $worksheet->getCell('F' . $row)->getCalculatedValue();
                $principal      = (float) $worksheet->getCell('G' . $row)->getCalculatedValue();
                $balance        = (float) $worksheet->getCell('I' . $row)->getCalculatedValue();
                $status         = $worksheet->getCell('J' . $row)->getValue();
                $collections[] = [
                    'payment_month'     => date("m/t/Y", strtotime($payment_month)),
                    'payment_amount'    => $payment_amount,
                    'interest'          => $interest,
                    'penalty'           => $penalty,
                    'principal'         => $principal,
                    'balance'           => $balance,
                    'status'            => $status ?? '',
                ];
                if ($payment_date_start == '' && $payment_amount > 0)
                    $payment_date_start = date("m/t/Y", strtotime($payment_month));
            }
            if ($payment_month == '')
                break;
        }

        // $loan_date = count($collections) > 0 ? date("Y-m-d", strtotime($collections[0]['payment_month'] . " -1 month")) : '';

        return [
            'loan_type_id'          => $loan_type_id,
            'loan_type_name'        => $loan_name,
            'loan_amount'           => (float) $loan_amount,
            'loan_interest'         => (float) $loan_interest,
            'loan_terms'            => 0,
            'loan_period'           => (float) $loan_period,
            'penalty_percentage'    => $penalty_percentage,
            'service_fee'           => 0,
            'payment_terms'         => $payment_terms,
            'loan_date'             => $loan_date,
            'payment_date_start'    => $payment_date_start,
            'monthly_payment'       => (float) $monthly_payment,
            'balance'               => (float) $balance,
            'collections'           => $collections
        ];
    }

    public function percentToNumericCell($cellValue)
    {
        if (strpos($cellValue, '%') !== false) {
            $cellValue = str_replace('%', '', $cellValue);
            $cellValue = (float) $cellValue;
        }
        return $cellValue;
    }

    public function save_upload()
    {
        try {
            $this->checker();
            $this->begin_transaction();

            $loan_data = $this->inputs['loan_data'];
            foreach ($loan_data as $clients) {
                if ($clients['client_id'] > 0)
                    if ($this->save_client_loans($clients) == null)
                        throw new Exception("save_upload: Error");
            }
            $this->commit();
            return 1;
        } catch (Exception $e) {
            $this->rollback();
            Logs::error("LoanUploads->save_upload", "Loans Upload", $e->getMessage());
            return 0;
        }
    }

    public function save_client_loans($client_data)
    {
        try {
            $client_id = $client_data['client_id'];
            $branch_id = $client_data['branch_id'];
            $response = [];
            $count = 1;

            foreach ($client_data['loans'] as $loan_data) {

                $reference_number  = "LN-$client_id-" . sprintf("%'.03d", $count++) . "-" . date("Ymdhis");
                $form_loan = array(
                    'reference_number'      => $reference_number,
                    'branch_id'             => $branch_id,
                    'client_id'             => $client_id,
                    'loan_type_id'          => $loan_data['loan_type_id'],
                    'loan_date'             => date("Y-m-d", strtotime($loan_data['loan_date'])),
                    'payment_date_start'    => date("Y-m-d", strtotime($loan_data['payment_date_start'])),
                    'loan_amount'           => $loan_data['loan_amount'],
                    'loan_period'           => $loan_data['loan_period'],
                    'loan_interest'         => $loan_data['loan_interest'],
                    'penalty_percentage'    => $loan_data['penalty_percentage'],
                    'service_fee'           => $loan_data['service_fee'],
                    'monthly_payment'       => $loan_data['monthly_payment'],
                    'payment_terms'         => $loan_data['payment_terms'],
                    'status'                => "R",
                    'is_imported'           => 1,
                );

                $loan_id = $this->insert($this->table, $form_loan, 'Y');
                if ($loan_id < 1)
                    throw new Exception("Insert Loan: ".$loan_id);

                if ($this->save_loan_collections($client_id, $branch_id, $loan_id, $loan_data) == null)
                    throw new Exception("Save Collection: Error");

                $response[] = $loan_id;
            }
            return $response;
        } catch (Exception $e) {
            Logs::error("LoanUploads->save_client_loans", "Loans Upload", $e->getMessage());
            return null;
        }
    }

    public function save_loan_collections($client_id, $branch_id, $loan_id, $loan_data)
    {
        try {
            $response = [];
            $collections = $loan_data['collections'];
            $count = 1;
            $count_index = 1;
            foreach ($collections as $collection_data) {
                if ($collection_data['payment_amount'] > 0) {
                    $reference_number = "CL-$client_id-" . sprintf("%'.03d", $count++) . "-" . $loan_id . "-" . date("Ymdhis");

                    $form = array(
                        'reference_number'  => $reference_number,
                        'loan_id'           => $loan_id,
                        'branch_id'         => $branch_id,
                        'chart_id'          => 0,
                        'client_id'         => $client_id,
                        'interest'          => $collection_data['interest'],
                        'amount'            => $collection_data['payment_amount'],
                        'collection_date'   => date("Y-m-d", strtotime($collection_data['payment_month'])),
                        'penalty_amount'    => $collection_data['penalty'],
                        'remarks'           => "",
                        'user_id'           => 0,
                        'atm_balance'       => 0,
                        'atm_withdrawal'    => 0,
                        'atm_charge'        => 0,
                    );
                    $collection_id = $this->insert("tbl_collections", $form, "Y");
                    if ($collection_id < 1)
                        throw new Exception("Form Collection: ".$collection_id." - ".json_encode($form));

                    if (strtoupper($collection_data['status']) == "RENEWAL") {

                        $last_loan_id = $loan_id;

                        $loan_reference_number  = "LN-$client_id-" . sprintf("%'.03d", $count++) . "-" . date("Ymdhis");
                        $form_loan = array(
                            'reference_number'      => $loan_reference_number,
                            'branch_id'             => $branch_id,
                            'client_id'             => $client_id,
                            'loan_type_id'          => $loan_data['loan_type_id'],
                            'loan_date'             => date("Y-m-d", strtotime($collection_data['payment_month'])),
                            'payment_date_start'    => date("Y-m-d", strtotime($collections[$count_index]['payment_month'])),
                            'loan_amount'           => $loan_data['loan_amount'],
                            'loan_period'           => $loan_data['loan_period'],
                            'loan_interest'         => $loan_data['loan_interest'],
                            'penalty_percentage'    => $loan_data['penalty_percentage'],
                            'service_fee'           => $loan_data['service_fee'],
                            'monthly_payment'       => $loan_data['monthly_payment'],
                            'payment_terms'         => $loan_data['payment_terms'],
                            'main_loan_id'          => $last_loan_id,
                            'renewal_status'        => "Y",
                            'deduct_to_loan'        => 1,
                            'status'                => "R",
                            'is_imported'           => 1,
                        );

                        $loan_id = $this->insert($this->table, $form_loan, 'Y');
                        if ($loan_id < 1)
                            throw new Exception($loan_id);

                        $this->update("tbl_loans", ["status" => 'F'], "loan_id = '$last_loan_id'");
                    }
                    $response[] = $collection_id;
                }
                $count_index++;
            }
            return count($response)>0?$response:1;//$response;
        } catch (Exception $e) {
            Logs::error("LoanUploads->save_loan_collections", "Loans Upload", $e->getMessage());
            return null;
        }
    }
}
