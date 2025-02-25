<form method='POST' id='frm_submit'>
    <div class="modal fade" id="modalEntry" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel"><span class='ion-compose'></span> Add Entry</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="hidden_id" name="input[insurance_id]">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label><strong style="color:red;">*</strong> Insurance</label>
                            <input type="text" class="form-control input-item" autocomplete="off" placeholder="Insurance name" name="input[insurance_name]" id="insurance_name" required>
                        </div>
                        <div class="form-group col-md-12">
                            <label><strong style="color:red;">*</strong> Amount</label>
                            <input type="number" step="0.1" class="form-control input-item" autocomplete="off" placeholder="Insurance amount" name="input[insurance_amount]" id="insurance_amount" required>
                        </div>
                        <div class="form-group col-md-12">
                            <label>Description</label>
                            <textarea class="form-control input-item" placeholder="Insurance desciption" name="input[insurance_desc]" id="insurance_desc"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-whitesmoke br">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" id="btn_submit" class="btn btn-primary">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>