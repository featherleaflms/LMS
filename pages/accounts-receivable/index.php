<section class="section">
    <div class="section-header">
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="#">Reports</a></div>
            <div class="breadcrumb-item">Accounts Receivable</div>
        </div>
    </div>

    <div class="section-body">
        <div class="alert alert-light alert-has-icon" style="border: 1px dashed #3C84AB;">
            <form id='frm_generate'>
                <div class="form-group row">
                <div class="col-lg-6">
                        <label><strong>Year</strong></label>
                        <select class="form-control select2" style="width: 100%;" id='report_year' name='report_year' required>
                            <?php
                            $year = date("Y") - 2;
                            for ($i = 0; $i <= 4; $i++) { ?>
                                <option value='<?php echo $year; ?>'
                                <?php if ($year == date("Y")) {
                                    echo 'selected';
                                } ?>>
                                    <?php echo $year; ?></option>;
                            <?php
                                $year++;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <label>&nbsp;</label>
                        <div>
                            <div class="btn-group pull-right">
                                <button type="submit" id="btn_generate" class="btn btn-primary btn-icon-split">
                                    <span class="icon">
                                        <i class="ti ti-reload"></i>
                                    </span>
                                    <span class="text"> Generate</span>
                                </button>
                                <button type="button" onclick="exportTableToExcel(this,'dt_entries','Accounts-Receivables')" class="btn btn-success btn-icon-split">
                                    <span class="icon">
                                        <i class="ti ti-cloud-down"></i>
                                    </span>
                                    <span class="text"> Export</span>
                                </button>
                                <button type="button" onclick="print_report('report_container')" class="btn btn-info btn-icon-split">
                                    <span class="icon">
                                        <i class="ti ti-printer"></i>
                                    </span>
                                    <span class="text"> Print</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="col-12 col-xl-12 card shadow mb-4">
                    <div id="report_container" class="card-body">
                        <center>
                            <img src="./assets/img/logo2.png" alt="logo" width="200"><br>
                            <h5>Accounts Receivable</h5><br>
                        </center>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dt_entries" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Reference #</th>
                                        <th>Client Name</th>
                                        <th>Loan Amount</th>
                                        <th>Total Payment</th>
                                        <th>Amount Receivable</th>
                                        <th>Total Penalties</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" style="text-align:right">Total:</th>
                                        <th><span id="span_total"></span></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $("#frm_generate").submit(function(e) {
        e.preventDefault();
        getReport();
    });


    function getReport() {
        var report_year = $("#report_year").val();
        $("#dt_entries").DataTable().destroy();
        $("#dt_entries").DataTable({
            "processing": true,
            "searching": false,
            "paging": false,
            "info": false,
            "ajax": {
                "url": "controllers/sql.php?c=" + route_settings.class_name + "&q=accounts_receivable",
                "dataSrc": "data",
                "method": "POST",
                "data": {
                    input: {
                        report_year: report_year
                    }
                },
            },
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api();

                // Remove the formatting to get integer data for summation
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                debitTotal = api
                    .column(7, {
                        page: 'current'
                    })
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer
                $(api.column(7).footer()).html(
                    "&#x20B1; " + debitTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2
                    })
                );


            },
            "columns": [{
                    "data": "loan_date"
                },
                {
                    "data": "reference_number"
                },
                {
                    "data": "client"
                },
                {
                    "data": "amount",
                    className: "text-right"
                },
                {
                    "data": "total_payment",
                    className: "text-right"
                },
                {
                    "data": "amount_receivable",
                    className: "text-right"
                },
                {
                    "data": "total_penalties",
                    className: "text-right"
                },
                {
                    "data": "subtotal",
                    className: "text-right"
                }
            ]

        });

    }

    $(document).ready(function() {
        getReport();
    });
</script>