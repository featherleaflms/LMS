<style>
    .label-print {
        font-weight: bold;
        text-transform: uppercase;
    }


    @media print {

        #approved_by {
            color: black;
        }


        .col-sm-1,
        .col-sm-2,
        .col-sm-3,
        .col-sm-4,
        .col-sm-5,
        .col-sm-6,
        .col-sm-7,
        .col-sm-8,
        .col-sm-9,
        .col-sm-10,
        .col-sm-11,
        .col-sm-12 {
            float: left;
        }

        .col-sm-12 {
            width: 100%;
        }

        .col-sm-11 {
            width: 91.66666667%;
        }

        .col-sm-10 {
            width: 83.33333333%;
        }

        .col-sm-9 {
            width: 75%;
        }

        .col-sm-8 {
            width: 66.66666667%;
        }

        .col-sm-7 {
            width: 58.33333333%;
        }

        .col-sm-6 {
            width: 50%;
        }

        .col-sm-5 {
            width: 41.66666667%;
        }

        .col-sm-4 {
            width: 33.33333333%;
        }

        .col-sm-3 {
            width: 25%;
        }

        .col-sm-2 {
            width: 16.66666667%;
        }

        .col-sm-1 {
            width: 8.33333333%;
        }
    }
</style>
<div class="modal fade" id="modalPrint" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #F5F5F5;">
                <h5 class="modal-title" id="modalLabel"><span class='fa fa-print'></span> Print Record</h5>
                
                <button type="button" onclick="print_report('print_canvas')" class="btn btn-primary">
                    <i class="bx bx-check d-block d-sm-none"></i>
                    <span><span class='fa fa-print'></span> Print</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 15px;">
                <div class="col-12" id="print_canvas">
                    <div class="row" style="display: flex;justify-content: center;">
                        <div><img src="./assets/img/logo2.png" alt="logo" width="150"></div>
                        <div style="padding-top: 27px;font-size: 16px;font-weight: bold;">FEATHERLEAF LENDING CORPORATION</div>
                    </div>
                    <input type="hidden" id="voucher_print_id">
                    <div class="row" style="padding-top: 20px;">
                        <div class="col-lg-12">
                            <label style="float:right;">No.: <span class="label-print" id="voucher_no_label_print"></span></label><br>
                            <center>
                                <h5>CHECK VOUCHER</h5>
                            </center>
                        </div>
                        <div class="col-lg-6">
                            <label>To: <span class="label-print" id="account_label_print"></span></label><br>
                        </div>
                        <div class="col-lg-6">
                            <label style="float: right;">Date: <span class="label-print" id="voucher_date_label_print"></span></label><br>
                        </div>
                        <div class="col-lg-12 table-responsive">
                            <table class="table table-bordered" width="100%">
                                <thead style="text-align: center;">
                                    <tr>
                                        <th style="border: 1px solid #E0E0E0;">DETAILS</th>
                                        <th style="border: 1px solid #E0E0E0;">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="border: 1px solid #E0E0E0;padding: 35px;">
                                            <center><span class="label-print" id="description_label_print"></span></center>
                                        </td>
                                        <td style="text-align:right;border: 1px solid #E0E0E0;padding: 35px;">
                                            &#8369;<span class="label-print" id="voucher_amount_label_print"></span>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td style="text-align:right;">TOTAL </td>
                                        <td style="text-align:right;font-weight: bold;">&#8369;<span style="text-decoration: underline;" id="total_amount"></span></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="col-sm-12"><i>Paid by:</i></div>
                        <div class="col-sm-6">
                            <label style="padding-left: 30px;">Check No.: <span class="label-print" id="check_number_label_print"></span></label>
                        </div>
                        <div class="col-sm-6">
                            <label>A/C No.: <span class="label-print" id="ac_no_label_print"></span></label>
                        </div>

                        <div class="col-sm-4">
                            <i>Prepared by:</i><br><br>
                            <span style="text-decoration: underline;padding-left: 30px;">&emsp;<?= strtoupper($User->fullname($_SESSION['lms_user_id'])); ?>&emsp;</span>
                        </div>
                        <div class="col-sm-4">
                            <i>Checked by:</i><br><br>
                            <span style="text-decoration: underline;padding-left: 30px;">&emsp;PRINCESS L. ARISCO&emsp;</span>
                        </div>
                        <div class="col-sm-4">
                            <i>Payment Approved:</i><br><br>
                                <div style="text-align:center;">
                                    <span class="span_approved" style="text-decoration: underline;padding-left: 30px;">
                                        <input type="text" id="approved_by" class="label-print" onchange="approvedBy()" style="margin: 20px;border-left: none;border-right: none;border-top: none;border-bottom:0.5px solid black;text-align: center;color:black;width:70%" autocomplete="off" />
                                    </span><br>
                                    <span><i>PRESIDENT</i></span>
                                </div>
                        </div>
                        <div class="col-sm-6 table-responsive">
                            <table id="dt_details_print" class="table" width="100%">
                                <thead style="text-align: center;">
                                    <tr>
                                        <th style="border: 1px solid #E0E0E0;">Account No.</th>
                                        <th style="border: 1px solid #E0E0E0;">Debit</th>
                                        <th style="border: 1px solid #E0E0E0;">Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-sm-6">
                            <hr style="margin-top: 0rem;">
                            <div style="text-align: right;">
                                <span class="label-print" id="cv_date_label_print"></span>
                            </div>
                            Received the sum of pesos <span class="label-print" style="text-decoration: underline;" id="amount_word_label_print"></span> in full payment of the above account.

                            <div style="text-align: right;padding-top: 100px;">
                                <span style="text-decoration: overline;padding-left: 30px;">
                                    &emsp;&emsp;&emsp;RECEIVED BY&emsp;&emsp;&emsp;
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function approvedBy() {
        var approved_by = $("#approved_by").val();
        var id = $("#voucher_print_id").val();
        document.getElementById("approved_by").setAttribute("value", approved_by);
        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=" + route_settings.class_name + "&q=update_approved_by",
            data: {
                input: {
                    approved_by: approved_by,
                    id: id
                }
            },
            success: function(data) {

            },
            error: function(jqXHR, textStatus, errorThrown) {
                errorLogger('Error:', textStatus, errorThrown);
            }
        });
    }

    function printRecord(id) {
        $("#modalPrint").modal('show');

        $.ajax({
            type: 'POST',
            url: "controllers/sql.php?c=" + route_settings.class_name + "&q=view",
            data: {
                input: {
                    id: id
                }
            },
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;

                $('.label-print').map(function() {
                    const id_name = this.id;
                    const new_id = id_name.replace('_label_print', '');
                    this.innerHTML = json[new_id];
                });

                $("#voucher_print_id").val(json['voucher_id']);
                $("#approved_by").val(json['approved_by']);
                document.getElementById("approved_by").setAttribute("value", json['approved_by']);

                $("#total_amount").html(json.voucher_amount);
                printJID(id);
            }
        });
    }

    function getPrintDetails(journal_entry_id) {
        var param = "journal_entry_id = '" + journal_entry_id + "'";
        $("#dt_details_print").DataTable().destroy();
        $("#dt_details_print").DataTable({
            "processing": true,
            "searching": false,
            "paging": false,
            "ordering": false,
            "info": false,
            "ajax": {
                "url": "controllers/sql.php?c=" + route_settings.class_name + "&q=show_detail",
                "dataSrc": "data",
                "type": "POST",
                "data": {
                    input: {
                        param: param
                    }
                }
            },
            "columns": [{
                    "data": "chart"
                },
                {
                    "data": "debit",
                    className: "text-right"
                },
                {
                    "data": "credit",
                    className: "text-right"
                },
            ]
        });
    }

    function printJID(id) {
        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=" + route_settings.class_name + "&q=journal_id",
            data: {
                input: {
                    id: id
                }
            },
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;
                getPrintDetails(json)
            }
        });
    }
</script>