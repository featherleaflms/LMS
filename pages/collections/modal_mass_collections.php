<form id='frmMassCollection' method="POST">
    <div class="modal fade" id="modalMassCollection" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" id="import_dialog" style="width: 100%;max-width: 2000px;margin: 0.5rem;">
            <div class="modal-content">
                <div class="modal-header" id="mass-modal-header">
                    <h5 class="modal-title"><span class='ion-compose'></span> Add Mass Collection</h5>
                </div>
                <div class="modal-body">
                    <div class="form-row w3-animate-left">
                        <div class="form-group col">
                            <label><strong style="color:red;">*</strong> Branch</label>
                            <select class="form-control select2 input-item" id="mass_branch_id" name="input[branch_id]" style="width:100%;" required>
                            </select>
                        </div>
                        <!--                         <div class="form-group col">
                            <label><strong style="color:red;">*</strong> Loan Type</label>
                            <select class="form-control select2 input-item" id="loan_type_id" name="input[loan_type_id]" style="width:100%;" required>
                            </select>
                        </div> -->
                        <div class="form-group col">
                            <label><strong style="color:red;">*</strong> Bank</label>
                            <select class="form-control select2 input-item" id="mass_chart_id" name="input[chart_id]" style="width:100%;" required>
                            </select>
                        </div>
                        <div class="form-group col">
                            <label><strong style="color:red;">*</strong> Collection Date</label>
                            <input type="date" class="form-control input-item" autocomplete="off" name="input[collection_date]" id="mass_collection_date" required>
                        </div>
                        <div class="form-group col">
                            <label><strong style="color:red;">*</strong> Employer</label>
                            <select class="form-control select2 input-item" id="mass_employer_id" name="input[employer_id]" style="width:100%;" required>
                            </select>
                        </div>
                        <div class="form-group col hide-for-save">
                            <label>ATM Charge</label>
                            <input min="0" type="number" class="form-control input-item" autocomplete="off" name="input[atm_charge]" id="mass_atm_charge">
                        </div>
                        <div class="form-group col hide-for-save">
                            <br />
                            <button type="submit" id="btn_mass_generate" style="margin-top:10px;" class="btn btn-primary">
                                Generate
                            </button>
                        </div>
                    </div>
                    <div class="row" id="mass_collection_result_content">
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="btn_mass_print" onclick="printMassCollection()" class="btn btn-primary">
                        Print
                    </button>
                    <!-- <button type="button" id="btn_mass_prev" class="btn btn-warning" onclick="goStep1()"><span class='fa fa-arrow-left'></span> Back</button> -->
                    <button type="button" id="btn_mass_save" onclick="saveMassCollection()" class="btn btn-primary">
                        Save
                    </button>
                    <button type="button" id="btn_mass_finish" onclick="finishCollection()" class="btn btn-success">
                        Finish
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    var mc_client_data = [],
        mc_header_data = [];
    document.addEventListener("keydown", function(event) {
        if (event.key === 'Enter') {
            const activeElement = document.activeElement;
            const editableElements = document.querySelectorAll('[contenteditable="true"]');
            const currentIndex = Array.from(editableElements).indexOf(activeElement);

            if (currentIndex !== -1) {
                const nextIndex = (currentIndex + 1) % editableElements.length;
                editableElements[nextIndex].focus();
                event.preventDefault(); // Prevent default behavior of the Enter key
            }
        }
    });

    function focusAndSelectText(myElement) {
        // Set the focus on the element
        myElement.focus();

        // Select the text content of the element
        if (document.body.createTextRange) { // For Internet Explorer
            const range = document.body.createTextRange();
            range.moveToElementText(myElement);
            range.select();
        } else if (window.getSelection) { // For modern browsers
            const selection = window.getSelection();
            const range = document.createRange();
            range.selectNodeContents(myElement);
            selection.removeAllRanges();
            selection.addRange(range);
        }
    }

    function focusFirstReceiptNumber() {
        const data_column = document.querySelectorAll(`[data-column='receipt_number']`);
        if (data_column.length > 0)
            focusAndSelectText(data_column[0]);
    }

    function addMassCollection() {
        $('#btn_mass_print').hide();
        $('#btn_mass_save').hide();
        $('#btn_mass_finish').hide();

        resetMassCollection();

        $("#mass-modal-header").html(`<h5 class="modal-title"><span class='ion-compose'></span> Add Mass Collection</h5>`);

        $('#modalMassCollection').modal({
            backdrop: 'static',
            keyboard: false
        }, 'show');
    }

    $('#frmMassCollection').submit(function(e) {
        e.preventDefault(); // Prevent form submission

        var formData = $(this).serialize();

        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=MassCollections&q=initialize",
            data: formData,
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;
                mc_header_data = json.headers;
                get_mass_collections(json);

                $('#btn_mass_save').show();
                $('#btn_mass_finish').show();
                $('#btn_mass_print').hide();
            }
        });
    });


    function saveMassCollection() {
        if (mc_client_data.length > 0) {
            if ($(".negative").length > 0) {
                swal("Cannot proceed!", "Negative values are found!", "warning");
            } else {

                $("#btn_mass_save").prop("disabled", true);
                $("#btn_mass_save").html("<span class='fa fa-spin fa-spinner'></span> Saving");

                var form_mass_collection = mc_header_data;
                form_mass_collection.details = mc_client_data;

                $.ajax({
                    type: 'POST',
                    url: "controllers/sql.php?c=MassCollections&q=save_collections",
                    data: JSON.stringify(form_mass_collection),
                    contentType: 'application/json',
                    success: function(response) {
                        const json = response.data;

                        $("#btn_mass_save").prop("disabled", false);
                        $("#btn_mass_save").html("Save");

                        $('#modalMassCollection').modal('hide');
                        if (json > 0) {
                            getEntries();
                            success_add();
                        } else {
                            failed_query("Please contact Juancoder IT Solutions");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText); // Handle any errors here
                    }
                });
            }
        } else {
            swal("Cannot proceed!", "No Entry found!", "warning");
        }
    }


    function finishCollection() {
        if (mc_client_data.length > 0) {
            if ($(".negative").length > 0) {
                swal("Cannot proceed!", "Negative values are found!", "warning");
            } else {

                $("#btn_mass_finish").prop("disabled", true);
                $("#btn_mass_finish").html("<span class='fa fa-spin fa-spinner'></span> Finishing");

                var form_mass_collection = mc_header_data;
                form_mass_collection.details = mc_client_data;

                $.ajax({
                    type: 'POST',
                    url: "controllers/sql.php?c=MassCollections&q=finish_collections",
                    data: JSON.stringify(form_mass_collection),
                    contentType: 'application/json',
                    success: function(response) {
                        const json = response.data;

                        $("#btn_mass_finish").prop("disabled", false);
                        $("#btn_mass_finish").html("Finish");

                        $('#modalMassCollection').modal('hide');
                        if (json > 0) {
                            getEntries();
                            success_add();
                        } else {
                            failed_query("Please contact Juancoder IT Solutions");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText); // Handle any errors here
                    }
                });
            }
        } else {
            swal("Cannot proceed!", "No Entry found!", "warning");
        }
    }

    function resetMassCollection() {
        getSelectOption2("mass_employer_id", "Employers", "employer_id", "employer_name", '', 0, 'Select All');
        getSelectOption2("mass_branch_id", "Branches", "branch_id", "branch_name", '', 0, 'Select All');
        getSelectOption2("mass_chart_id", "ChartOfAccounts", "chart_id", "chart_name", "chart_name LIKE '%Bank%'");

        $("#mass_chart_id").html($("#chart_id").html()).val(null).select2().trigger('change').prop("disabled", false);
        $("#mass_branch_id").html($("#branch_id").html()).val(null).select2().trigger('change').prop("disabled", false);
        // $("#loan_type_id").val(0).select2().trigger('change').prop("disabled", false);
        $("#mass_employer_id").val(0).select2().trigger('change').prop("disabled", false);
        $("#mass_collection_date").val('').prop("disabled", false).attr('readonly', false);
        $("#mass_atm_charge").val('').prop("disabled", false).attr('readonly', false);

        $(".hide-for-save").show();

        $('#mass_collection_result_content').html(`<div style='width:100%' class='w3-animate-left'>
            <table id="tbl_mass_collection">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th class='w-8'>Receipt #</th>
                <th class='w-8'>ATM Balance Before Withdrawal</th>
                <th class='w-8'>ATM Withdrawal</th>
                <th class='w-8'>Deduction</th>
                <th class='w-8'>Emergency Loan</th>
                <th class='w-8'>ATM Charge</th>
                <th class='w-8'>ATM Balance</th>
                <th class='w-8'>Excess</th>
                <th>Account Number</th>
                <th>Status</th>
              </tr>
              <tr>
                <th colspan="3" class="end">TOTAL:</th>
                <th id="mc_total_2" class="right"></th>
                <th id="mc_total_3" class="right"></th>
                <th id="mc_total_4" class="right"></th>
                <th id="mc_total_5" class="right"></th>
                <th id="mc_total_6" class="right"></th>
                <th id="mc_total_7" class="right"></th>
                <th id="mc_total_8" class="right"></th>
                <th></th>
                <th></th>
              </tr>
        </table>`);
    }

    function get_mass_collections(json) {
        var client_tds = "";
        mc_client_data = [];

        var loan_types = json.headers.loan_types,
            skin_loan_types = "",
            skin_total_loan_types = "";

        var total_old_atm_balance = 0,
            total_atm_withdrawal = 0,
            total_deduction = 0,
            total_emergency_loan = 0,
            total_atm_charge = 0,
            total_atm_balance = 0,
            total_excess = 0;
        for (var clientIndex = 0; clientIndex < json.clients.length; clientIndex++) {
            const client = json.clients[clientIndex];

            var receipt_number = client.receipt_number;
            var old_atm_balance = client.old_atm_balance * 1;
            var atm_withdrawal = client.atm_withdrawal * 1;
            var deduction = getLoanTotalDeduction(client.loans);
            var atm_charge = client.atm_charge * 1;
            var atm_balance = client.atm_balance * 1;
            var excess = atm_withdrawal - deduction - atm_charge;
            var atm_account_no = client.atm_account_no;
            var is_included = client.is_included * 1;
            var nega_excess = excess < 0 ? "negative" : "";
            var excluded_loan = is_included == 0 ? "excluded_loan" : "";
            var checked_loan_status = is_included == 0 ? "" : "checked";
            var is_editable = is_included == 0 ? false : true;

            var client_data = {
                mass_collection_detail_id: client.mass_collection_detail_id * 1,
                client_id: client.client_id * 1,
                branch_id: client.branch_id * 1,
                old_atm_balance: old_atm_balance,
                atm_withdrawal: atm_withdrawal,
                atm_charge: atm_charge,
                atm_balance: atm_balance,
                excess: excess,
                receipt_number: receipt_number,
                atm_account_no: atm_account_no,
                is_included: is_included,
                loans: client.loans
            };

            total_old_atm_balance += old_atm_balance;
            total_atm_withdrawal += atm_withdrawal;
            total_deduction += deduction;
            total_atm_charge += atm_charge;
            total_atm_balance += atm_balance;
            total_excess += excess;

            mc_client_data.push(client_data);
            client_tds += `<tr data-client-index='${clientIndex}' class='${excluded_loan}'>
                <td class="sticky-column">${clientIndex + 1}</td>
                <td class="center sticky-column checked-input"><input class='checkbox-loan' type='checkbox' onchange="changeMassCollectionLoanStatus(this)" ${checked_loan_status}></td>
                <td class="sticky-column" style="font-size:9pt !important;min-width:150px !important;">${client.client_name}</td>
                <td data-column='receipt_number' onblur="editCollectionCell(this,false)" contenteditable="${is_editable}" class='right editable-cell'>${receipt_number}</td>
                <td data-column='old_atm_balance' onblur="editCollectionCell(this)" contenteditable="${is_editable}" class='right editable-cell'>${numberFormatClearZero(old_atm_balance)}</td>
                <td data-column='atm_withdrawal' onblur="editCollectionCell(this)" contenteditable="${is_editable}" class='right editable-cell'>${numberFormatClearZero(atm_withdrawal)}</td>
                ${skinLoanTypes(client.loans)}
                <td data-column='atm_charge' onblur="editCollectionCell(this)" contenteditable="${is_editable}" class='right editable-cell'>${numberFormatClearZero(atm_charge)}</td>
                <td data-column='atm_balance' class='right'>${numberFormatClearZero(atm_balance)}</td>
                <td data-column='excess' class='right ${nega_excess}'>${numberFormatClearZero(excess)}</td>
                <td data-column='atm_account_no' class='center'>${atm_account_no}</td>
              </tr>`;
        }

        for (var loanTypeIndex = 0; loanTypeIndex < loan_types.length; loanTypeIndex++) {
            var loan_type = loan_types[loanTypeIndex];
            skin_loan_types += `<th class='w-8'>${loan_type.loan_type}</th>`;
            skin_total_loan_types += `<th data-column='total_loan_type_${loan_type.loan_type_id}' class="right"></th>`;
        }
        $('#mass_collection_result_content').html(`<div class='w3-animate-left table-container' style="width:100%;">
            <table id="tbl_mass_collection">
                <thead>
                  <tr>
                    <th class="sticky-column">#</th>
                    <th class="sticky-column checked-input"><input type='checkbox' onchange='checkAllLoan(this)' checked></th>
                    <th class="sticky-column">Name</th>
                    <th class='w-8'>Receipt #</th>
                    <th class='w-8 fs-9'>ATM Balance Before Withdrawal</th>
                    <th class='w-8'>ATM Withdrawal</th>
                    ${skin_loan_types}
                    <th class='w-8'>ATM Charge</th>
                    <th class='w-8'>ATM Balance</th>
                    <th class='w-8'>Excess</th>
                    <th>Account Number</th>
                  </tr>
                </thead>
              ${client_tds}
              <tr class='table-footer'>
                <th class="checked-input"></th>
                <th colspan="3" class="end">TOTAL:</th>
                <th data-column='total_old_atm_balance' class="right">${numberFormat(total_old_atm_balance)}</th>
                <th data-column='total_atm_withdrawal' class="right">${numberFormat(total_atm_withdrawal)}</th>
                ${skin_total_loan_types}
                <th data-column='total_atm_charge' class="right">${numberFormat(total_atm_charge)}</th>
                <th data-column='total_atm_balance' class="right">${numberFormat(total_atm_balance)}</th>
                <th data-column='total_excess' class="right">${numberFormat(total_excess)}</th>
                <th></th>
              </tr>
        </table>
        </div>
        <div class='col-md-12 row' style="color:#0a0a0a;" id="mass_users">
            <div class="form-group col">
                <label>PREPARED BY:</label>
                <select onchange="changeUsers(this)" data-column="prepared_by" id="prepared_by" class="form-control select2-mass" style="width:100%;" required>${optionUsers(json.headers.users, json.headers.prepared_by)}</select>
            </div>
            <div class="form-group col">
                <label>CHECKED BY:</label>
                <select onchange="changeUsers(this)" data-column="finished_by" id="finished_by" class="form-control select2-mass" style="width:100%;" required>${optionUsers(json.headers.users, json.headers.finished_by)}</select>
            </div>
        </div>`);
        focusFirstReceiptNumber();
        totalLoanSolvers(loan_types);

        // <td data-column='deduction' onblur="editCollectionCell(this)" contenteditable="${is_editable}" class='right editable-cell'>${numberFormatClearZero(deduction)}</td>
        // <td data-column='emergency_loan' onblur="editCollectionCell(this)" contenteditable="${is_editable}" class='right editable-cell'>${numberFormatClearZero(emergency_loan)}</td>
        $('.select2-mass').select2();
    }


    function totalLoanSolvers(loan_types) {
        for (var loanTypeIndex = 0; loanTypeIndex < loan_types.length; loanTypeIndex++) {
            var loan_type = loan_types[loanTypeIndex];
            var column_data = `loan_type_${loan_type.loan_type_id}`;
            totalSolvers(column_data);
        }
    }

    function optionUsers(users, user_id = 0) {
        var option = "<option value=''>&mdash; Please Select &mdash; </option>";
        for (var userIndex = 0; userIndex < users.length; userIndex++) {
            var user = users[userIndex];
            var selected = user.user_id == user_id ? "selected" : "";
            option += `<option ${selected} value='${user.user_id}'>${user.user_fullname}</option>`;
        }
        return option;
    }

    function changeUsers(el) {
        var column = el.getAttribute("data-column");
        mc_header_data[column] = el.value;
    }

    function skinLoanTypes(loans) {
        var skin_loans = "";
        for (var loanIndex = 0; loanIndex < loans.length; loanIndex++) {
            var loan = loans[loanIndex];
            var is_editable = (loan.loan_id) * 1 > 0;
            var cell_class = is_editable ? "editable-cell" : "gray";
            skin_loans += `<td data-column='loan_type_${loan.loan_type_id}' data-loan-index='${loanIndex}' data-type-id='${loan.loan_type_id}' onblur="editLoanCollectionCell(this)" contenteditable="${is_editable}" class='right ${cell_class}'>${numberFormatClearZero(loan.monthly_payment)}</td>`;
        }
        return skin_loans;
    }

    function editCollectionCell(el, is_number = true) {
        var str = el.innerText;
        if (is_number) {
            var replace_number = parseFloat(str.replaceAll(",", ""));
            var actual_data = replace_number ? replace_number : 0;
            el.innerHTML = numberFormatClearZero(actual_data);
        } else {
            el.innerHTML = str;
            var actual_data = str;
        }

        var column = el.getAttribute("data-column");
        var client_index = el.parentNode.getAttribute("data-client-index");

        mc_client_data[client_index][column] = actual_data;

        if (is_number) {
            totalSolvers(column);
            collectionSolvers(el, client_index);
        }
    }

    function editLoanCollectionCell(el, is_number = true) {
        var str = el.innerText;

        var replace_number = parseFloat(str.replaceAll(",", ""));
        var actual_data = replace_number ? replace_number : 0;
        el.innerHTML = numberFormatClearZero(actual_data);

        var column = el.getAttribute("data-column");
        var loan_index = el.getAttribute("data-loan-index");
        var client_index = el.parentNode.getAttribute("data-client-index");

        mc_client_data[client_index].loans[loan_index].monthly_payment = actual_data;

        totalSolvers(column);
        collectionSolvers(el, client_index);

    }

    function totalSolvers(column) {
        // Get all elements with the attribute data-column='excess'
        const data_column = document.querySelectorAll(`[data-column='${column}']`);

        // Loop through the NodeList and perform actions on each element
        var total_value = 0;
        for (let i = 0; i < data_column.length; i++) {
            const element = data_column[i];

            var str = element.innerHTML;
            var replace_number = parseFloat(str.replaceAll(",", ""));
            var actual_data = replace_number ? replace_number : 0;

            total_value += actual_data;
        }
        const total_data_column = document.querySelector(`[data-column='total_${column}']`);
        total_data_column.innerHTML = numberFormatClearZero(total_value);
    }

    function collectionSolvers(el, client_index) {
        var old_atm_balance = mc_client_data[client_index].old_atm_balance * 1;
        var atm_withdrawal = mc_client_data[client_index].atm_withdrawal * 1;
        var deduction = getLoanTotalDeduction(mc_client_data[client_index].loans);
        var atm_charge = mc_client_data[client_index].atm_charge * 1;

        var atm_balance = old_atm_balance - atm_withdrawal;
        var excess = atm_withdrawal - deduction - atm_charge;

        mc_client_data[client_index].atm_balance = atm_balance;
        mc_client_data[client_index].excess = excess;

        // Find the sibling td elements with data-column='excess' within the same row
        const excess_column = el.parentNode.querySelector("td[data-column='excess']");
        excess_column.innerHTML = numberFormatClearZero(excess);
        negativeIdentifier(excess_column, excess);
        totalSolvers('excess');

        const atm_balance_column = el.parentNode.querySelector("td[data-column='atm_balance']");
        atm_balance_column.innerHTML = numberFormatClearZero(atm_balance);
        negativeIdentifier(atm_balance_column, atm_balance);
        totalSolvers('atm_balance');
    }

    function resetLoanDeduction(client_index) {
        var loans = mc_client_data[client_index].loans;
        for (var loanIndex = 0; loanIndex < loans.length; loanIndex++) {
            mc_client_data[client_index].loans[loanIndex].monthly_payment = 0;
        }
    }

    function getLoanTotalDeduction(loans) {
        var total_deduction = 0;
        for (var loanIndex = 0; loanIndex < loans.length; loanIndex++) {
            var loan = loans[loanIndex];
            total_deduction += loan.monthly_payment;
        }
        return total_deduction;
    }

    function negativeIdentifier(el, value) {
        if (value >= 0) {
            el.classList.remove('negative');
        } else {
            el.classList.add('negative');
        }
    }

    function changeMassCollectionLoanStatus(el) {
        var client_index = el.parentNode.parentNode.getAttribute("data-client-index");
        if (el.checked) {
            el.parentNode.parentNode.classList.remove('excluded_loan');
            mc_client_data[client_index].is_included = 1;

            modifyEditableCell(el, true);
        } else {
            el.parentNode.parentNode.classList.add('excluded_loan');
            mc_client_data[client_index].is_included = 0;

            mc_client_data[client_index].receipt_number = '';
            mc_client_data[client_index].old_atm_balance = 0;
            mc_client_data[client_index].atm_withdrawal = 0;
            mc_client_data[client_index].deduction = 0;
            mc_client_data[client_index].emergency_loan = 0;
            mc_client_data[client_index].atm_charge = 0;
            mc_client_data[client_index].atm_balance = 0;
            mc_client_data[client_index].excess = 0;

            resetLoanDeduction(client_index);
            modifyEditableCell(el);
            collectionSolvers(el.parentNode, client_index);
        }
    }

    function modifyEditableCell(el, editable = false) {
        var parent_node = el.parentNode.parentNode;
        var client_index = parent_node.getAttribute("data-client-index");
        const data_column = parent_node.querySelectorAll('td.editable-cell');

        var total_value = 0;
        for (let i = 0; i < data_column.length; i++) {
            const element = data_column[i];
            element.innerHTML = '';
            element.setAttribute('contenteditable', editable);
            var column_data = element.getAttribute('data-column');
            if (column_data != 'receipt_number')
                totalSolvers(column_data);
        }
    }

    function checkAllLoan(ele) {
        var checkboxes = document.getElementsByClassName('checkbox-loan');
        if (ele.checked) {
            for (var i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = true;
                    changeMassCollectionLoanStatus(checkboxes[i]);
                }
            }
        } else {
            swal({
                    title: 'Are you sure?',
                    text: 'Your data will be cleared!',
                    icon: 'warning',
                    buttons: ["Cancel", "Proceed"],
                    dangerMode: true,
                })
                .then((willProceed) => {
                    if (willProceed) {
                        for (var i = 0; i < checkboxes.length; i++) {
                            //console.log(i)
                            if (checkboxes[i].type == 'checkbox') {
                                checkboxes[i].checked = false;
                                changeMassCollectionLoanStatus(checkboxes[i]);
                            }
                        }
                    } else {
                        ele.checked = true;
                    }
                });
        }
    }
</script>
<script>
    function viewMassCollection(mass_collection_id, is_finish = false) {
        getSelectOption2("mass_employer_id", "Employers", "employer_id", "employer_name", '', 0, 'Select All');
        getSelectOption2("mass_branch_id", "Branches", "branch_id", "branch_name", '', 0, 'Select All');
        getSelectOption2("mass_chart_id", "ChartOfAccounts", "chart_id", "chart_name", "chart_name LIKE '%Bank%'");
        $(".hide-for-save").hide();

        is_finish ? $('#btn_mass_save').hide() : $('#btn_mass_save').show();
        is_finish ? $('#btn_mass_finish').hide() : $('#btn_mass_finish').show();
        is_finish ? $('#btn_mass_print').show() : $('#btn_mass_print').hide();

        $("#modalSavedMassCollection").modal("hide");
        $("#mass-modal-header").html(`<h5 class="modal-title"><span class='ion-compose'></span> View Saved Mass Collection</h5>`);

        $('#modalMassCollection').modal({
            backdrop: 'static',
            keyboard: false
        }, 'show');

        var form_data = {
            input: {
                id: mass_collection_id
            },
        }

        $.ajax({
            type: "POST",
            url: "controllers/sql.php?c=MassCollections&q=view_saved",
            data: form_data,
            success: function(data) {
                var jsonParse = JSON.parse(data);
                const json = jsonParse.data;
                mc_header_data = json.headers;

                // $("#loan_type_id").val(mc_header_data.loan_type_id).select2().trigger('change').prop("disabled", true);
                $("#mass_employer_id").val(mc_header_data.employer_id).select2().trigger('change').prop("disabled", true);
                $("#mass_chart_id").val(mc_header_data.chart_id).select2().trigger('change').prop("disabled", true);
                $("#mass_branch_id").val(mc_header_data.branch_id).select2().trigger('change').prop("disabled", true);

                $("#mass_collection_date").val(mc_header_data.collection_date).prop("disabled", true);

                get_mass_collections(json);
            }
        });
    }

    function printMassCollection() {
        var original_html = $("#mass_collection_result_content").html();
        var prepared_by_ = $("#prepared_by option:selected").text();
        var finished_by_ = $("#finished_by option:selected").text();
        $(".checked-input").remove();
        $("#mass_users").html('');

        var myWindow = window.open('', 'Print Mass Collection', 'height=600,width=2500');
        myWindow.document.write('<html><head><title>Print Mass Collection</title>');
        myWindow.document.write('<style>#tbl_mass_collection{font-family:arial,sans-serif;font-size:10pt;border-collapse:collapse;width:100%;color:#0a0a0a}.table-container{max-height:300px;overflow:auto}.table-container thead{background-color:#f2f2f2;font-weight:bold;}#tbl_mass_collection td,th{border:1px solid #ddd;padding:2px}#tbl_mass_collection th{text-align:center;font-size:8pt!important}#tbl_mass_collection td{font-size:11pt!important}.table-footer{font-size:12pt!important}.import_failed{background-color:#db5151;color:#fff}.w-10{width:10%!important}.w-8{width:8% !important}.w-5{width:5%!important}.fs-9{font-size:9px!important}.right{text-align:right!important}.center{text-align:center!important}.end{text-align:end!important}.negative{background:red;color:#fff}.gray{background:gray;color:#fff}.excluded_loan{background:gray;color:white;text-decoration:line-through}.form-group{width:50%;}.row{width:100%;}</style>');
        /*optional stylesheet*/ //myWindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
        myWindow.document.write('</head><body>');
        // myWindow.document.write('<div align="center" style="font-size:12pt; font-weight:bold; width: 100%;">');
        // myWindow.document.write(data_header + '<br></div>');
        myWindow.document.write(`<div>
            <span>FEATHERLEAF LENDING CORP.</span><br>
            <span>DATE: ${$("#mass_collection_date").val()}</span><br>
            <span>BANK: ${$("#mass_chart_id option:selected").text()}</span>
        </div>`);
        myWindow.document.write('<div>' + $("#mass_collection_result_content").html() + "</div>");
        myWindow.document.write(`<div>
            <span>Prepared by: <b>${prepared_by_}</b></span><br>
            <span>Checked by: <b>${finished_by_}</b></span>
        </div>`);
        myWindow.document.write('</body>');
        myWindow.document.write('</html>');
        myWindow.document.close();
        myWindow.print();
        $("#mass_collection_result_content").html(original_html);
    }
</script>
<style>
    #tbl_mass_collection {
        font-family: arial, sans-serif;
        font-size: 10pt;
        border-collapse: collapse;
        width: 100%;
        color: #0a0a0a;
    }

    .table-container {
        /* Set a max height to make the table scrollable */
        max-height: 300px;
        overflow: auto;
    }

    .table-container thead {
        position: sticky;
        top: 0;
        background-color: #f2f2f2;
        /* Set the background color for the sticky header */
        font-weight: bold;
        /* Optionally, you can style the sticky header */
        z-index: 2;
    }


    .sticky-column {
        position: sticky;
        left: 0;
        z-index: 1;
        /*        background-color: #f9f9f9;*/
    }

    #tbl_mass_collection td,
    th {
        border: 1px solid #dddddd;
        padding: 2px;
    }

    #tbl_mass_collection th {
        text-align: center;
        font-size: 8pt !important;
    }

    #tbl_mass_collection td {
        font-size: 11pt !important;
    }

    .table-footer {
        font-size: 12pt !important;
    }

    .import_failed {
        background-color: #db5151;
        color: #fff;
    }

    .w-10 {
        width: 10% !important;
    }

    .w-8 {
        width: 8% !important;
    }

    .w-5 {
        width: 5% !important;
    }

    .fs-9 {
        font-size: 9px !important;
    }

    .right {
        text-align: right !important;
    }

    .center {
        text-align: center !important;
    }

    .end {
        text-align: end !important;
    }

    .negative {
        background: red;
        color: #fff;
    }

    .gray {
        background: gray;
        color: #fff;
    }

    .excluded_loan {
        background: gray;
        color: white;
        text-decoration: line-through;
    }
</style>

<style>
    .w3-animate-left {
        position: relative;
        animation: animateleft 0.8s
    }

    @keyframes animateleft {
        from {
            left: -300px;
            opacity: 0
        }

        to {
            left: 0;
            opacity: 1
        }
    }

    .w3-animate-zoom {
        animation: animatezoom 0.8s
    }

    @keyframes animatezoom {
        from {
            transform: scale(0)
        }

        to {
            transform: scale(1)
        }
    }

    .w3-animate-top {
        position: relative;
        animation: animatetop 0.4s
    }

    @keyframes animatetop {
        from {
            top: -300px;
            opacity: 0
        }

        to {
            top: 0;
            opacity: 1
        }
    }
</style>