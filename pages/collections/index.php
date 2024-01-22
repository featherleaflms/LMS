<section class="section">
    <div class="section-header">
        <div class="alert-body">
            <div class="alert-title">Collections</div>
            Manage collections here.
        </div>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="#">Transactions</a></div>
            <div class="breadcrumb-item">Collections</div>
        </div>
    </div>
    <div class="section-body shadow">
        <div class="alert alert-light alert-has-icon" style="border: 1px dashed #3C84AB;">
            <div>
                <div class="form-group row">
                    <div class="col-md-3">
                        <label><strong>Start Date</strong></label>
                        <div>
                            <input type="date" required class="form-control" id="start_date"
                                value="<?php echo date('Y-m-01', strtotime(date(" Y-m-d"))); ?>"
                            name="input[start_date]">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label><strong>End Date</strong></label>
                        <div>
                            <input type="date" required class="form-control" id="end_date"
                                value="<?php echo date('Y-m-t', strtotime(date(" Y-m-d"))) ?>" name="input[end_date]">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>&nbsp;</label>
                        <div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning" onclick="getEntries()"><i
                                        class="fas fa-refresh"></i> Generate</button>

                                <div class="btn-group btn-group" role="group" aria-label="Basic example">
                                    <a href="#" data-toggle="dropdown" class="btn btn-info dropdown-toggle"><i
                                            class="fas fa-file-excel"></i> Template</a>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item has-icon" onclick="exportTemplate()"><i
                                                class="fas fa-download"></i> Export</a>
                                        <a href="#" class="dropdown-item has-icon" onclick="importTemplate()"><i
                                                class="far fa-upload"></i> Import</a>
                                    </div>
                                </div>
                                <div class="btn-group btn-group" role="group" aria-label="Basic example">
                                    <a href="#" data-toggle="dropdown" class="btn btn-success dropdown-toggle"><i
                                            class="fas fa-coins"></i> Mass Collection</a>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item has-icon" onclick="addMassCollection()"><i
                                                class="fas fa-plus"></i> Add</a>
                                        <a href="#" class="dropdown-item has-icon"
                                            onclick="viewSavedMassCollection()"><i class="far fa-list"></i> View
                                            Saved</a>
                                    </div>
                                </div>
                                <div class="btn-group btn-group" role="group" aria-label="Basic example">
                                    <button type="button" class="btn btn-primary" onclick="addModalCollections()"><i
                                            class="fas fa-plus"></i>
                                        Add</button>
                                    <button type="button" class="btn btn-danger" onclick="deleteEntry()"><i
                                            class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- <a href="#" data-toggle="dropdown" class="btn btn-info dropdown-toggle"><i class="fas fa-file-excel"></i> Template</a>
        <div class="dropdown-menu">
            <a href="#" class="dropdown-item has-icon" onclick="exportTemplate()"><i class="fas fa-download"></i> Export</a>
            <a href="#" class="dropdown-item has-icon" onclick="importTemplate()"><i class="far fa-upload"></i> Import</a>
        </div>
        <div class="btn-group btn-group" role="group" aria-label="Basic example">
            <button type="button" class="btn btn-success" onclick="addMassCollection()"><i class="fas fa-coins"></i> Mass Collection</button>
            <button type="button" class="btn btn-primary" onclick="addModal()"><i class="fas fa-plus"></i>
                Add</button>
            <button type="button" class="btn btn-danger" onclick="deleteEntry()"><i class="fas fa-trash"></i> Delete</button>
        </div> -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dt_entries" class="table table-striped">
                                <thead class="">
                                    <tr>
                                        <th style="width:10px;">
                                            <div class="custom-checkbox custom-control">
                                                <input type="checkbox" data-checkboxes="mygroup"
                                                    class="custom-control-input" id="checkbox-1"
                                                    onchange="checkAll(this, 'dt_id')">
                                                <label for="checkbox-1" class="custom-control-label">&nbsp;</label>
                                            </div>
                                        </th>
                                        <th></th>
                                        <th>Reference #</th>
                                        <th>Loan ID</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Collection Date</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                        <th>Date Modified</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include "modal_collections.php"; ?>
<?php include "modal_mass_collections.php"; ?>
<?php include "modal_saved_mass_collections.php"; ?>
<?php include "modal_export.php"; ?>
<?php include "modal_import.php"; ?>
<script type="text/javascript">
    function print_collection_solo(){
        var id = $("#hidden_id").val();

        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=" + route_settings.class_name + "&q=print",
            data: {
                input: {
                    id: id
                }
            },
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;
                
                $("#temporary_print").html(`
                    <label style='font-weight:900;'><center>Collection <br>
                    Featherleaf <br>
                    ${json.branch_name} <br>
                    ${json.reference_number} <br><br><br>
                    </center></label>
                                <label><strong>Collection Date:</strong> ${json.collection_date}</label><br>
                                <label><strong>Client:</strong> ${json.client_name}</label><br>
                                <label><strong>Receipt #: ${json.receipt_number}</strong></label><br>
                                <label><strong>Bank:</strong> ${json.coa_name}</label><br>
                                        
                                <label><strong>Penalty:</strong> ${numberFormat(json.penalty_amount,2)}</label><br>
                                <label><strong>Amount:</strong> ${numberFormat(json.amount,2)}</label><br>

                                <label><strong>Atm Balance Before Withdrawal:</strong> ${numberFormat(json.old_atm_balance,2)}</label><br>
                                <label><strong>Atm Withdrawal:</strong> ${numberFormat(json.atm_withdrawal,2)}</label><br>

                                <label><strong>Atm Charge:</strong> ${numberFormat(json.atm_charge,2)}</label><br>
                                <label><strong>Atm Balance:</strong> ${numberFormat(json.atm_balance,2)}</label><br>
                                <label><strong>Excess:</strong> ${numberFormat(json.excess,2)}</label><br>
                                <label><strong>Remarks:</strong> ${json.remarks}</label><br>
                `);

        var divContents = document.getElementById("temporary_print").innerHTML;
            var a = window.open('', '', 'height=500, width=500');
            a.document.write('<html>');
            a.document.write('<link rel="stylesheet" href="assets/modules/datatables/datatables.min.css"><link rel="stylesheet" href="assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css"><link rel="stylesheet" href="assets/modules/datatables/Select-1.2.4/css/select.bootstrap4.min.css"><link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/components.css"><link rel="stylesheet" href="assets/modules/select2/dist/css/select2.min.css">')
            a.document.write('<body>');
            a.document.write(divContents);
            a.document.write('</body></html>');
            a.document.close();
            a.print();
            }
        });
    }

    function getEntries() {
        var start_date = $("#start_date").val();
        var end_date = $("#end_date").val();
        var param = "(collection_date >= '" + start_date + "' AND collection_date <= '" + end_date + "')";

        $("#dt_entries").DataTable().destroy();
        $("#dt_entries").DataTable({
            "processing": true,
            "order": [
                [8, 'desc']
            ],
            "ajax": {
                "url": "controllers/sql.php?c=" + route_settings.class_name + "&q=show",
                "dataSrc": "data",
                "type": "POST",
                "data": {
                    input: {
                        param: param
                    }
                }
            },
            "columns": [{
                "mRender": function(data, type, row) {
                    return '<div class="custom-checkbox custom-control"><input type="checkbox" data-checkboxes="mygroup" class="custom-control-input" name="dt_id" id="checkbox-b' + row.collection_id + '" value=' + row.collection_id + '><label for="checkbox-b' + row.collection_id + '" class="custom-control-label">&nbsp;</label></div>';
                }
            },
            {
                "mRender": function(data, type, row) {
                    return "<center><button class='btn btn-sm btn-info' onclick='getEntryDetailsCollection(" + row.collection_id + ")'><span class='fa fa-edit'></span></button></center>";
                }
            },
            {
                "data": "reference_number"
            },
            {
                "data": "loan_ref_id"
            },
            {
                "data": "client"
            },
            {
                "data": "amount"
            },
            {
                "data": "collection_date"
            },
            {
                "mRender": function(data, type, row) {
                    return row.status === 'P' ? ('<a href="#" class="badge badge-light">Pending</a>') : (row.status === 'F' ? '<a href="#" class="badge badge-success">Finished</a>' : '<a href="#" class="badge badge-danger">Canceled</a>');

                }
            },
            {
                "data": "date_added"
            },
            {
                "data": "date_last_modified"
            }
            ]
        });
    }

    function addModalCollections() {
        modal_detail_status = 0;

        $("#branch_id").html('').val('');
        $("#client_id").html('').val('');
        $("#loan_id").html('').val('');
        $("#chart_id").html('').val('');
        $("#monthly_payment_span").html('');

        getSelectOption('Branches', 'branch_id', 'branch_name');
        getSelectOption('ChartOfAccounts', 'chart_id', 'chart_name', "chart_name LIKE '%Bank%'");

        $("#hidden_id").val(0);
        document.getElementById("frm_submit").reset();


        $('.select2').select2().trigger('change');

        var element = document.getElementById('reference_number');
        if (typeof (element) != 'undefined' && element != null) {
            generateReference(route_settings.class_name);
        }

        $('.input-item').attr('readonly', false);
        $(".select2").prop("disabled", false);
        $("#btn_submit").show();

        $("#modalLabel").html("<i class='fa fa-edit'></i> Add Entry");
        $("#modalEntry").modal('show');
    }

    function getEntryDetailsCollection(id) {
        // $('.select-item').map(function() {
        //     $(this).off("change");
        // });
        // $("#collection_date").off('change');
        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=" + route_settings.class_name + "&q=view",
            data: {
                input: {
                    id: id
                }
            },
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;

                $("#hidden_id").val(id);

                // $('.select2').select2().trigger('change');

                getSelectOption('Branches', 'branch_id', 'branch_name', "", [], '', 'Please Select', '', '', json.branch_id);
                getSelectOption('Clients', 'client_id', 'client_fullname', "client_id='" + json.client_id + "'", [], '', 'Please Select', '', '', json.client_id);
                getSelectOption('Loans', 'loan_id', "reference_number", "loan_id = '" + json.loan_id + "'", [], '', 'Please Select', '', '', json.loan_id);
                getSelectOption('ChartOfAccounts', 'chart_id', 'chart_name', "chart_name LIKE '%Bank%'", [], '', 'Please Select', '', '', json.chart_id);


                $('.input-item').map(function() {
                    const id_name = this.id;
                    this.value = json[id_name];
                    $("#" + id_name).val(json[id_name]);
                });

                $('.check-item').map(function() {
                    const id_name = this.id;
                    if (json[id_name] == "Yes") {
                        $("#" + id_name).prop("checked", true);
                    } else {
                        $("#" + id_name).prop("checked", false);
                    }
                });

                loanDetails(json.loan_id);

                $("#loan_amount_span").html(json['loan_amount']);
                $('.input-item').attr('readonly', true);
                $(".select2").prop("disabled", true);
                $("#btn_submit").hide();
                $("#btn_print").show();

                $("#modalLabel").html("<i class='flaticon-edit'></i> Update Entry");
                $("#modalEntry").modal('show');
            }
        });
    }

    function getPenalty() {
        var loan_id = $("#loan_id").val();
        var collection_date = $("#collection_date").val();
        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=Loans&q=penalty",
            data: {
                input: {
                    loan_id: loan_id,
                    collection_date: collection_date
                }
            },
            success: function(data) {
                var json = JSON.parse(data);
                $("#penalty_amount").val(json.data);
            }
        });
    }

    function loan_id() {
        var id = $("#hidden_id").val();
        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=" + route_settings.class_name + "&q=loan_id",
            data: {
                input: {
                    id: id
                }
            },
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;
                $("#loan_id").val(json).trigger('change');
                loanDetails();

            }
        });
    }


    function loanDetails(_loan_id = 0) {

        var loan_id = _loan_id > 0 ? _loan_id : $("#loan_id").val() * 1;
        _loan_id > 0 ? '' : getPenalty();
        getloanDetails(loan_id);

        var param = "loan_id= '" + loan_id + "' ";
        $("#dt_loan_details").DataTable().destroy();
        $("#dt_loan_details").DataTable({
            "processing": true,
            "searching": false,
            "paging": false,
            "ordering": false,
            "info": false,
            "order": [
                [2, 'desc']
            ],
            "ajax": {
                "url": "controllers/sql.php?c=Loans&q=soa_collection",
                "dataSrc": "data",
                "method": "POST",
                "data": {
                    input: {
                        param: param
                    }
                },
            },
            "columns": [{
                "data": "date"
            },
            {
                "data": "payment"
            },
            {
                "data": "interest"
            },
            {
                "data": "penalty"
            },
            {
                "data": "applicable_principal"
            },
            {
                "data": "balance"
            }
            ]
        });
        // }
    }

    function getloanDetails(id) {

        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=Loans&q=view",
            data: {
                input: {
                    id: id
                }
            },
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;
                $("#loan_amount_span").html(json['amount']);
                $("#monthly_payment_span").html(json['monthly_payment_amount']);
            }
        });
    }

    function getLoans() {
        var client_id = $("#client_id").val();
        getSelectOption('Loans', 'loan_id', "reference_number", "client_id = '" + client_id + "' AND status = 'R'");
    }

    function getClients() {
        var branch_id = $("#branch_id").val();
        getSelectOption('Clients', 'client_id', 'client_fullname', "branch_id='" + branch_id + "'");

    }

    function clients() {
        var id = $("#hidden_id").val();
        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=" + route_settings.class_name + "&q=client_id",
            data: {
                input: {
                    id: id
                }
            },
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;
                $("#client_id").val(json[0]).trigger('change');
                loan_id();
            }
        });
    }

    function atmComputers() {
        var old_atm_balance = $("#old_atm_balance").val() * 1;
        var atm_withdrawal = $("#atm_withdrawal").val() * 1;
        var atm_charge = $("#atm_charge").val() * 1;
        var deduction = $("#amount").val() * 1;
        var atm_balance = old_atm_balance - atm_withdrawal;
        var excess = atm_withdrawal - deduction - atm_charge;
        $("#atm_balance").val(atm_balance);
        $("#excess").val(excess);
    }

    $(document).ready(function() {
        schema();
        getEntries();
        getSelectOption('Branches', 'branch_id', 'branch_name');
        getSelectOption('ChartOfAccounts', 'chart_id', 'chart_name', "chart_name LIKE '%Bank%'");
    });
</script>