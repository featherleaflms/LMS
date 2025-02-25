<?php
include 'core/config.php';

if (!isset($_SESSION['lms_user_id'])) {
  header("location:./login.php");
}

require_once 'routes/init.routes.php';

$User = new Users;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Juancoder IT Solutions</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link href="assets/modules/fontawesome/css/fontawesome.css" rel="stylesheet">
  <link href="assets/modules/fontawesome/css/brands.css" rel="stylesheet">
  <link href="assets/modules/fontawesome/css/solid.css" rel="stylesheet">

  <!-- CSS Libraries -->
  <!-- <link rel="stylesheet" href="assets/modules/jqvmap/dist/jqvmap.min.css">
  <link rel="stylesheet" href="assets/modules/weather-icon/css/weather-icons.min.css">
  <link rel="stylesheet" href="assets/modules/weather-icon/css/weather-icons-wind.min.css"> -->
  <link rel="stylesheet" href="assets/modules/datatables/datatables.min.css">
  <link rel="stylesheet" href="assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="assets/modules/datatables/Select-1.2.4/css/select.bootstrap4.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/modules/select2/dist/css/select2.min.css">


  <script src="assets/modules/jquery.min.js"></script>

  <link rel="shortcut icon" href="assets/img/jcis_logo.png" />

  <!-- JS Libraies -->
  <script src="assets/modules/datatables/datatables.min.js"></script>
  <script src="assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js"></script>
  <script src="assets/modules/datatables/Select-1.2.4/js/dataTables.select.min.js"></script>

  <!-- Start GA -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-94034622-3');
  </script>
  <!-- /END GA -->
  <style>
    @media (min-width: 768px) {
      .modal-xl {
        width: 90%;
        max-width: 1200px;
      }

    }

    @media print {
      input[type="text"] {
        color: black !important;
      }
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #444;
      line-height: 42px;
    }

    label {
      font-weight: bold !important;
    }

    .form-control {
      border-color: #BDBDBD;
    }

    .modal-body {
      max-height: 500px !important;
      overflow: auto !important;
    }
  </style>
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar">
        <form class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
            <!-- <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li> -->
          </ul>
          <div class="search-element">
            <!-- <input class="form-control" type="search" placeholder="Search" aria-label="Search" data-width="250">
            <button class="btn" type="submit"><i class="fas fa-search"></i></button>
            <div class="search-backdrop"></div> -->

          </div>
        </form>
        <ul class="navbar-nav navbar-right">

          <!-- <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg beep"><i class="far fa-bell"></i></a>
            <div class="dropdown-menu dropdown-list dropdown-menu-right">
              <div class="dropdown-header">Notifications
                <div class="float-right">
                  <a href="#">Mark All As Read</a>
                </div>
              </div>
              <div class="dropdown-list-content dropdown-list-icons">
                <a href="#" class="dropdown-item dropdown-item-unread">
                  <div class="dropdown-item-icon bg-primary text-white">
                    <i class="fas fa-code"></i>
                  </div>
                  <div class="dropdown-item-desc">
                    Template update is available now!
                    <div class="time text-primary">2 Min Ago</div>
                  </div>
                </a>
                <a href="#" class="dropdown-item">
                  <div class="dropdown-item-icon bg-info text-white">
                    <i class="far fa-user"></i>
                  </div>
                  <div class="dropdown-item-desc">
                    <b>You</b> and <b>Dedik Sugiharto</b> are now friends
                    <div class="time">10 Hours Ago</div>
                  </div>
                </a>
                <a href="#" class="dropdown-item">
                  <div class="dropdown-item-icon bg-success text-white">
                    <i class="fas fa-check"></i>
                  </div>
                  <div class="dropdown-item-desc">
                    <b>Kusnaedi</b> has moved task <b>Fix bug header</b> to <b>Done</b>
                    <div class="time">12 Hours Ago</div>
                  </div>
                </a>
                <a href="#" class="dropdown-item">
                  <div class="dropdown-item-icon bg-danger text-white">
                    <i class="fas fa-exclamation-triangle"></i>
                  </div>
                  <div class="dropdown-item-desc">
                    Low disk space. Let's clean it!
                    <div class="time">17 Hours Ago</div>
                  </div>
                </a>
                <a href="#" class="dropdown-item">
                  <div class="dropdown-item-icon bg-info text-white">
                    <i class="fas fa-bell"></i>
                  </div>
                  <div class="dropdown-item-desc">
                    Welcome to Stisla template!
                    <div class="time">Yesterday</div>
                  </div>
                </a>
              </div>
              <div class="dropdown-footer text-center">
                <a href="#">View All <i class="fas fa-chevron-right"></i></a>
              </div>
            </div>
          </li> -->
          <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
              <img alt="image" src="assets/img/avatar/avatar-1.png" class="rounded-circle mr-1">
              <div class="d-sm-none d-lg-inline-block">Hi,
                <?= $User->fullname($_SESSION['lms_user_id']); ?>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
              <a href="profile" class="dropdown-item has-icon">
                <i class="far fa-user"></i> Profile
              </a>
              <!-- <a href="features-activities.html" class="dropdown-item has-icon">
                <i class="fas fa-bolt"></i> Activities
              </a> -->
              <div class="dropdown-divider"></div>
              <a href="#" onclick="logout()" class="dropdown-item has-icon text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
              </a>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <?php include "components/sidebar.php"; ?>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <?php require 'routes/routes.php'; ?>
      </div>
      <footer class="main-footer">
        <div class="footer-left">
          Copyright &copy;
          <script>
            document.write(new Date().getFullYear());
          </script>
          <div class="bullet"></div> <a href="https://juancoder.com/">Juancoder IT Solutions</a>
        </div>
        <div class="footer-right">

        </div>
      </footer>
    </div>
  </div>
  <script type='text/javascript'>
    <?= "var route_settings = " . $route_settings . ";\n"; ?>
    <?= "var company_name = 'Featherleaf Lending Corporation'";
    ?>
  </script>
  <script type="text/javascript">
    var modal_detail_status = 0;
    $(document).ready(function() {
      // $('body').on('shown.bs.modal', '.modal', function() {
      //   $(this).find('select').each(function() {
      //     $(this).select2({
      //       dropdownParent: $('.modal')
      //     });
      //   });
      // });
    });

    function print_report(container) {

      var printContents = document.getElementById(container).innerHTML;
      var originalContents = document.body.innerHTML;
      document.body.innerHTML = printContents;
      window.print();

      document.body.innerHTML = originalContents;

      location.reload();
    }

    function logout() {
      swal({
          title: 'Are you sure?',
          text: 'Your session will expire!',
          icon: 'warning',
          buttons: ["Cancel", "Logout"],
          dangerMode: true,
        })
        .then((willDelete) => {
          if (willDelete) {
            var url = "controllers/sql.php?c=Users&q=logout";
            $.ajax({
              url: url,
              success: function(data) {
                location.reload();
              }
            });
          }
        });
    }

    function schema() {
      $.ajax({
        type: "POST",
        url: "controllers/sql.php?c=" + route_settings.class_name + "&q=schema",
        data: [],
        success: function(data) {
          var json = JSON.parse(data);
          console.log(json.data);
        }
      });
    }

    function success_add() {
      swal("Success!", "Successfully added entry!", "success");
    }

    function success_finish() {
      swal("Success!", "Successfully finished!", "success");
    }

    function success_update() {
      swal("Success!", "Successfully updated entry!", "success");
    }

    function success_delete() {
      swal("Success!", "Successfully deleted entry!", "success");
    }

    function success_cancel() {
      swal("Success!", "Successfully cancelled entry!", "success");
    }

    function success_upload() {
      swal("Success!", "Successfully uploaded!", "success");
    }

    function entry_already_exists() {
      swal("Cannot proceed!", "Entry already exists!", "warning");
    }

    function amount_is_greater() {
      swal("Cannot proceed!", "Amount is greater than balance!", "warning");
    }

    function failed_query(data) {
      swal("Failed to execute query!", data, "warning");
      //alert('Something is wrong. Failed to execute query. Please try again.');
    }

    function numberFormatClearZero(y, n = 2) {
      y = y * 1;
      if (y == 0)
        return '';
      x = y.toFixed(n);
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function numberFormat(y, n = 2) {
      y = y * 1;
      x = y.toFixed(n);
      return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    function checkAll(ele, ref) {
      var checkboxes = document.getElementsByName(ref);
      if (ele.checked) {
        for (var i = 0; i < checkboxes.length; i++) {
          if (checkboxes[i].type == 'checkbox') {
            checkboxes[i].checked = true;
          }
        }
      } else {
        for (var i = 0; i < checkboxes.length; i++) {
          //console.log(i)
          if (checkboxes[i].type == 'checkbox') {
            checkboxes[i].checked = false;
          }
        }
      }
    }


    function addModal() {
      modal_detail_status = 0;
      $("#hidden_id").val(0);
      document.getElementById("frm_submit").reset();

      $('.select2').select2().trigger('change');

      var element = document.getElementById('reference_number');
      if (typeof(element) != 'undefined' && element != null) {
        generateReference(route_settings.class_name);
      }

      if (route_settings.class_name == "Vouchers") {
        $("#journal_id").val(7).trigger('change');
      } else if (route_settings.class_name == "Loans") {
        $('#loan_container .form-control').attr('readonly', false);
        $(".select2").prop("disabled", false);
        $("#div_sample_calculation").show();
        $("#div_soa").hide();
        sampleCalculation('add');
        $("#btn_submit").show();
        $("#btn_sample_cal").show();
        // $("#btn_release").hide();
        // $("#btn_reloan").hide();

      } else if (route_settings.class_name == "Collections") {
        $('.input-item').attr('readonly', false);
        $(".select2").prop("disabled", false);
        $("#btn_submit").show();

      }


      $("#modalLabel").html("<i class='fa fa-edit'></i> Add Entry");
      $("#modalEntry").modal('show');
    }

    $("#frm_submit").submit(function(e) {
      e.preventDefault();

      var old_submit_html = $("#btn_submit").html();

      $("#btn_submit").prop('disabled', true);
      $("#btn_submit").html("<span class='fa fa-spinner fa-spin'></span> Submitting ...");

      var hidden_id = $("#hidden_id").val();
      var q = hidden_id > 0 ? "edit" : "add";
      $.ajax({
        type: "POST",
        url: "controllers/sql.php?c=" + route_settings.class_name + "&q=" + q,
        data: $("#frm_submit").serialize(),
        success: function(data) {
          getEntries();
          var json = JSON.parse(data);
          if (route_settings.has_detail == 1) {
            if (json.data > 0) {
              $("#modalEntry").modal('hide');

              hidden_id > 0 ? success_update() : success_add();
              hidden_id > 0 ? $("#modalEntry2").modal('hide') : '';
              hidden_id > 0 ? getEntryDetails2(hidden_id) : getEntryDetails2(json.data);
            } else if (json.data == -2) {
              entry_already_exists();
            } else {
              failed_query(json);
            }
          } else {
            if (json.data == 1) {
              hidden_id > 0 ? success_update() : success_add();
              $("#modalEntry").modal('hide');
            } else if (json.data == 2) {
              entry_already_exists();
            } else {
              failed_query(json.data);
            }
          }

          $("#btn_submit").prop('disabled', false);
          $("#btn_submit").html(old_submit_html);
        },
        error: function(jqXHR, textStatus, errorThrown) {
          errorLogger('Error:', textStatus, errorThrown);
        }
      });
    });

    function getEntryDetails(id, is_det = 0) {
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

          $('.select2').select2().trigger('change');
          $('.input-item').map(function() {
            const id_name = this.id;
            this.value = json[id_name];
            $("#" + id_name).val(json[id_name]).trigger('change');
          });

          $('.check-item').map(function() {
            const id_name = this.id;
            if (json[id_name] == "Yes") {
              $("#" + id_name).prop("checked", true);
            } else {
              $("#" + id_name).prop("checked", false);
            }
          });


          if (route_settings.class_name == "Clients") {

            c_status = "update";
            $(".client_span").html(jsonParse.data['client_fullname']);
            var clienttypes = jsonParse.data['client_type_id'].split(',').map(Number);
            $("#client_type_id").val(clienttypes).trigger('change');
            // $("#client_type_id").select2().select2('val', []);
          } else if (route_settings.class_name == "Loans") {
            $("#loan_amount_span").html(json['amount']);

            $("#div_amount").html('<label><strong style="color:red;">*</strong> Loan amount</label><input type="number" step="0.01" class="form-control input-item" onchange="calculateInterest()" autocomplete="off" name="input[loan_amount]" id="loan_amount" required>');

            $("#btn_sample_cal").hide();
            $("#monthly_payment_span").html(json['monthly_payment']);
            if (jsonParse.data['status'] != "A") {
              $('#loan_container :input').attr('readonly', true);
              $(".select2").prop("disabled", true);
              $("#btn_submit").hide();

            } else if (jsonParse.data['status'] == "A") {
              $("#btn_submit").show();
              // $("#btn_release").show();
              // $("#btn_reloan").hide();
            }
            clients();

            $("#div_sample_calculation").hide();
            $("#div_soa").show();
            loanDetails(2);
            $("#hidden_id_2").val(id);

            $("#loan_amount").val(json['loan_amount']);
            $("#loan_amount").val(json['loan_amount']).trigger('change');
          } else if (route_settings.class_name == "LoanTypes") {
            if (json['fixed_interest'] != "Y") {
              $("#fixed_interest").prop("checked", false);
            } else {
              $("#fixed_interest").prop("checked", true);
            }
            fixedInterest();

          }


          $("#modalLabel").html("<i class='flaticon-edit'></i> Update Entry");
          $("#modalEntry").modal('show');
        }
      });

      if (is_det == 1) {
        $("#modalEntry2").modal('hide');
      } else {
        modal_detail_status = 0;
      }
    }


    function deleteEntry() {

      var count_checked = $("input[name='dt_id']:checked").length;

      if (count_checked > 0) {
        swal({
            title: 'Are you sure?',
            text: 'You will not be able to recover these entries!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              var checkedValues = $("input[name='dt_id']:checked").map(function() {
                return this.value;
              }).get();

              $.ajax({
                type: "POST",
                url: "controllers/sql.php?c=" + route_settings.class_name + "&q=remove",
                data: {
                  input: {
                    ids: checkedValues
                  }
                },
                success: function(data) {
                  getEntries();
                  var json = JSON.parse(data);
                  console.log(json);
                  if (json.data == 1) {
                    success_delete();
                  } else {
                    failed_query(json);
                  }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                  errorLogger('Error:', textStatus, errorThrown);
                }
              });

              $("#btn_delete").prop('disabled', true);
            } else {
              swal("Cancelled", "Entries are safe :)", "error");
            }
          });
      } else {
        swal("Cannot proceed!", "Please select entries to delete!", "warning");
      }
    }

    // MODULE WITH DETAILS LIKE SALES

    function getEntryDetails2(id) {
      $("#hidden_id_2").val(id);

      modal_detail_status = 1;
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

          $('.label-item').map(function() {
            const id_name = this.id;
            const new_id = id_name.replace('_label', '');
            this.innerHTML = json[new_id];
          });


          if (route_settings.class_name == "Vouchers") {
            journalID(id);

            if (json.status == 'F') {
              $("#menu-cancel-transaction").show();
            } else {
              $("#menu-cancel-transaction").hide();
            }
          }

          var transaction_edit = document.getElementById("menu-edit-transaction");
          var transaction_delete_items = document.getElementById("menu-delete-selected-items");
          var transaction_finish = document.getElementById("menu-finish-transaction");
          var col_list = document.getElementById("col-list");
          var col_item = document.getElementById("col-item");


          if (json.status == 'F') {
            transaction_edit.classList.add('disabled');
            (typeof(transaction_delete_items) != 'undefined' && transaction_delete_items != null) ? transaction_delete_items.classList.add('disabled'): '';
            transaction_finish.classList.add('disabled');

            transaction_edit.setAttribute("onclick", "");
            (typeof(transaction_delete_items) != 'undefined' && transaction_delete_items != null) ? transaction_delete_items.setAttribute("onclick", ""): '';
            transaction_finish.setAttribute("onclick", "");

            (typeof(col_item) != 'undefined' && col_item != null) ? col_item.style.display = "none": '';
            (typeof(col_list) != 'undefined' && col_list != null) ? col_list.classList.remove('col-8'): '';
            (typeof(col_list) != 'undefined' && col_list != null) ? col_list.classList.add('col-12'): '';
          } else {
            transaction_edit.classList.remove('disabled');
            (typeof(transaction_delete_items) != 'undefined' && transaction_delete_items != null) ? transaction_delete_items.classList.remove('disabled'): '';
            transaction_finish.classList.remove('disabled');

            transaction_edit.setAttribute("onclick", "getEntryDetails(" + id + ",1)");
            (typeof(transaction_delete_items) != 'undefined' && transaction_delete_items != null) ? transaction_delete_items.setAttribute("onclick", "deleteEntry2()"): '';
            transaction_finish.setAttribute("onclick", "finishTransaction()");

            (typeof(col_item) != 'undefined' && col_item != null) ? col_item.style.display = "block": '';
            (typeof(col_list) != 'undefined' && col_list != null) ? col_list.classList.remove('col-12'): '';
            (typeof(col_list) != 'undefined' && col_list != null) ? col_list.classList.add('col-8'): '';
          }
          getEntries2();

          $("#modalEntry2").modal('show');
        }
      });
    }

    $("#frm_submit_2").submit(function(e) {
      e.preventDefault();

      $("#btn_submit_2").prop('disabled', true);
      $("#btn_submit_2").html("<span class='fa fa-spinner fa-spin'></span> Submitting ...");

      $.ajax({
        type: "POST",
        url: "controllers/sql.php?c=" + route_settings.class_name + "&q=add_detail",
        data: $("#frm_submit_2").serialize(),
        success: function(data) {
          getEntries2();
          var json = JSON.parse(data);
          if (json.data == 1) {
            success_add();
            document.getElementById("frm_submit_2").reset();
            $('.select2').select2().trigger('change');
          } else if (json.data == 2) {
            entry_already_exists();
          } else if (json.data == 3) {
            amount_is_greater();
          } else {
            failed_query(json);
            $("#modalEntry2").modal('hide');
          }
          $("#btn_submit_2").prop('disabled', false);
          $("#btn_submit_2").html("Submit");
        }
      });
    });

    function deleteEntry2() {

      var count_checked = $("input[name='dt_id_2']:checked").length;

      if (count_checked > 0) {

        $("#btn_delete_member").prop("disabled", true);
        $("#btn_delete_member").html("<span class='fa fa-spinner fa-spin'></span>");
        swal({
            title: 'Are you sure?',
            text: 'You will not be able to recover these entries!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              var checkedValues = $("input[name='dt_id_2']:checked").map(function() {
                return this.value;
              }).get();

              $.ajax({
                type: "POST",
                url: "controllers/sql.php?c=" + route_settings.class_name + "&q=remove_detail",
                data: {
                  input: {
                    ids: checkedValues
                  }
                },
                success: function(data) {
                  getEntries2();
                  var json = JSON.parse(data);
                  console.log(json);
                  if (json.data == 1) {
                    success_delete();
                  } else {
                    failed_query(json);
                  }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                  errorLogger('Error:', textStatus, errorThrown);
                }
              });
            } else {
              swal("Cancelled", "Entries are safe :)", "error");
            }
            $("#btn_delete_member").prop('disabled', false);
            $("#btn_delete_member").html('<i class = "fas fa-trash"> </i> Delete');
          });
      } else {
        swal("Cannot proceed!", "Please select entries to delete!", "warning");
      }
    }

    function finishTransaction() {
      var id = $("#hidden_id_2").val();

      var count_checked = $("input[name='dt_id_2']").length;
      if (count_checked > 0) {
        swal({
            title: 'Are you sure?',
            text: 'This entries will be finished!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
          })
          .then((willDelete) => {
            if (willDelete) {
              $.ajax({
                type: "POST",
                url: "controllers/sql.php?c=" + route_settings.class_name + "&q=finish",
                data: {
                  input: {
                    id: id
                  }
                },
                success: function(data) {
                  getEntries();
                  var json = JSON.parse(data);
                  if (json.data == 1) {
                    success_finish();
                    $("#modalEntry2").modal('hide');
                  } else if (json.data == -1) {
                    swal("Cannot proceed!", "Total debt is not equivalent to total credit.", "warning");
                  } else if (json.data == -2) {
                    swal("Cannot proceed!", "The total does not match the voucher amount.", "warning");
                  } else {
                    failed_query(json);
                  }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                  errorLogger('Error:', textStatus, errorThrown);
                }
              });
            } else {
              swal("Cancelled", "Entries are safe :)", "error");
            }
          });
      } else {
        swal("Cannot proceed!", "No entries found!", "warning");
      }
    }
    // END MODULE

    function getSelectOption(class_name, primary_id, label, param = '', attributes = [], pre_value = '', pre_label = 'Please Select', sub_option = '', is_class = '', selected = 0) {
      $("#" + primary_id).prepend($('<option></option>').html('Loading...'));
      $.ajax({
        type: "POST",
        url: "controllers/sql.php?c=" + class_name + "&q=show",
        data: {
          input: {
            param: param
          }
        },
        success: function(data) {
          var json = JSON.parse(data);
          if (pre_value != "remove") {
            if (is_class == '') {
              $("#" + primary_id).html("<option value='" + pre_value + "'> &mdash; " + pre_label + " &mdash; </option>");
            } else {
              $("." + primary_id).html("<option value='" + pre_value + "'> &mdash; " + pre_label + " &mdash; </option>");
            }
          }

          for (list_index = 0; list_index < json.data.length; list_index++) {
            const list = json.data[list_index];
            var data_attributes = {};
            if (sub_option == 1) {
              data_attributes['value'] = list[primary_id.slice(0, -2)];
            } else {
              data_attributes['value'] = list[primary_id];
            }
            if (data_attributes['value'] == selected) {
              data_attributes['selected'] = true;
            }
            for (var attr_index in attributes) {
              const attr = attributes[attr_index];
              data_attributes[attr] = list[attr];
            }

            if (is_class == '') {
              $('#' + primary_id).append($("<option></option>").attr(data_attributes).text(list[label]));
            } else {
              $('.' + primary_id).append($("<option></option>").attr(data_attributes).text(list[label]));
            }
          }
        }
      });
    }

    function getSelectOption2(element_id, class_name, value_name, text_name, param = '', pre_value = '', pre_text = 'Please Select', selected = 0, attributes = []) {
      $("#" + element_id).prepend($('<option></option>').html('Loading...'));
      $.ajax({
        type: "POST",
        url: "controllers/sql.php?c=" + class_name + "&q=show",
        data: {
          input: {
            param: param
          }
        },
        success: function(data) {
          var json = JSON.parse(data);

          $("#" + element_id).html("<option value='" + pre_value + "'> &mdash; " + pre_text + " &mdash; </option>");


          for (list_index = 0; list_index < json.data.length; list_index++) {
            const list = json.data[list_index];
            var data_attributes = {};

            data_attributes['value'] = list[value_name];

            if (data_attributes['value'] == selected) {
              data_attributes['selected'] = true;
            }
            for (var attr_index in attributes) {
              const attr = attributes[attr_index];
              data_attributes[attr] = list[attr];
            }


            $('#' + element_id).append($("<option></option>").attr(data_attributes).text(list[text_name]));

          }
        }
      });
    }

    function generateReference(class_name) {
      $.ajax({
        type: "POST",
        url: "controllers/sql.php?c=" + class_name + "&q=generate",
        data: [],
        success: function(data) {
          var json = JSON.parse(data);
          $("#reference_number").val(json.data);
          $(".reference_number").val(json.data);
        },
        error: function(jqXHR, textStatus, errorThrown) {
          errorLogger('Error:', textStatus, errorThrown);
        }
      });
    }

    function exportTableToExcel(el, tableID = 'dt_entries', filename = '') {

      $(el).prop('disabled', true);

      filename = filename ? filename + '.xls' : 'excel_data.xls';

      var htmls = "";
      var uri = 'data:application/vnd.ms-excel;base64,';
      var template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>';
      var base64 = function(s) {
        return window.btoa(unescape(encodeURIComponent(s)))
      };

      var format = function(s, c) {
        return s.replace(/{(\w+)}/g, function(m, p) {
          return c[p];
        })
      };

      var table = document.getElementById(tableID).createCaption();

      var header_text = '';
      $('.report-header').map(function() {
        header_text += this.innerHTML + "<br>";
      });

      table.innerHTML = header_text + "<br>";

      htmls = $("#" + tableID).html();
      document.getElementById(tableID).deleteCaption();
      var ctx = {
        worksheet: 'Worksheet',
        table: htmls
      }


      var link = document.createElement("a");
      link.download = filename;
      link.href = uri + base64(format(template, ctx));
      link.click();

      myTimeout = setTimeout(function() {
        $(el).prop('disabled', false)
      }, 1000);
    }


    function printCanvas() {
      var printContents = document.getElementById('print_canvas').innerHTML;
      var originalContents = document.body.innerHTML;
      $("#approved_by").show();
      document.body.innerHTML = printContents;
      window.print();
      document.body.innerHTML = originalContents;
      window.close();
      location.reload();
    }
  </script>

  <!-- General JS Scripts -->
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>

  <!-- JS Libraies -->
  <script src="assets/modules/sweetalert/sweetalert.min.js"></script>

  <!-- Page Specific JS File -->
  <script src="assets/modules/select2/dist/js/select2.full.min.js"></script>
  <!-- <script src="assets/js/page/modules-sweetalert.js"></script> -->


  <!-- Page Specific JS File -->
  <script src="assets/js/page/index.js"></script>

  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

</body>

</html>