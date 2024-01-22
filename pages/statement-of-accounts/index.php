<section class="section">
    <div class="section-header">
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="#">Reports</a></div>
            <div class="breadcrumb-item">Statement of Accounts</div>
        </div>
    </div>

    <div class="section-body">
        <div class="alert alert-light alert-has-icon" style="border: 1px dashed #3C84AB;">
            <form id='frm_generate'>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label><strong style="color:red;">*</strong> <strong>Client</strong></label>
                        <select style="width:100%;" class="form-control form-control-sm select2" required id="client_id" name="input[client_id]">
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>&nbsp;</label>
                        <div>
                            <div class="btn-group pull-right">
                                <button type="submit" id="btn_generate" class="btn btn-primary btn-icon-split">
                                    <span class="icon">
                                        <i class="ti ti-reload"></i>
                                    </span>
                                    <span class="text"> Generate</span>
                                </button>
                                <button type="button" onclick="exportTableToExcel(this,'dt_entries','Statement-of-Accounts')" class="btn btn-success btn-icon-split">
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
            <div class="col-md-12">
                <div class="col-12 col-xl-12 card shadow mb-4">
                    <div id="report_container" class="card-body">

                        <center>
                            <img src="./assets/img/logo2.png" alt="logo" width="200"><br>
                            <h5>Statement of Accounts</h5>
                            <h6><span class="span_details" id="span_client"></span></h6>
                        </center>

                        <div class="row table-responsive" id="soa_report">
                            <div class="col-md-12 table-responsive">
                                <table class="table table-bordered" id="dt_entries" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Payment Date</th>
                                            <th>Payment</th>
                                            <th>Interest Amount</th>
                                            <th>Penalty</th>
                                            <th>Applicable to Principal</th>
                                            <th>Balance Outstanding</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th colspan="3"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
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
        var client_id = $("#client_id").val();
        var param = "client_id= '" + client_id + "'AND status !='F'  AND (renewal_status != 'N' OR main_loan_id='0')"; //(status!='D' OR status!='P')

        $("#soa_report").html("<br><center><h3><span class='fa fa-spinner fa-spin'></span> Loading ...</h3></center>");

        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=" + route_settings.class_name + "&q=report",
            data: {
                input: {
                    param: param
                }
            },
            success: function(data) {
                $("#soa_report").html(data.replace('{"data":null}', ''));
                
            }
        });

        getClient();
    }


    function getDetails(id) {
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

                $('.span_details').map(function() {
                    //console.log(this.id);
                    const id_name = this.id;
                    this.innerHTML = json[id_name];
                });
            }
        });
    }

    function getClient() {
        //var optionSelected = $("#client_id").find('option:selected').attr('client_fullname');
        var id = $("#client_id").val();
        var optionSelected = $("#client_id option[value="+id+"]").text();
        $("#span_client").html(optionSelected);
    }

    $(document).ready(function() {
        // getSelectOption('Clients', 'client_id', 'client_fullname', '', ['client_fullname']);
        getSelectOption('Clients', 'client_id', 'client_fullname');
    });
</script>