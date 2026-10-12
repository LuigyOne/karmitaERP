<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Report &mdash; <?= get_compnay_title(); ?></title>
    <?php include('include-css.php'); ?>

</head>

<body>
    <!-- Please add all report sections to language file from reporting and report-details view pages -->
    <!-- 1. view reporting file 2. controller reporting file 3. reporting_model file 4. common.js and custom.js 5. change asset if report file 
    6. report-detals file 7. reporting and report_media sql files 8. report folder in asset 9. report-details.js file-->
    <div id="app">
        <div class="main-wrapper main-wrapper-1">

            <?php include('include-header.php'); ?>

            <!-- Main Content -->
            <div class="main-content"> 
                <section class="section">
                    <div class="section-header">
                        <h1><?= !empty($this->lang->line('label_report')) ? $this->lang->line('label_report') : 'Report'; ?></h1>
                        <div class="section-header-breadcrumb">

                            <i class="btn btn-primary btn-rounded no-shadow" id="modal-add-report"><?= !empty($this->lang->line('label_create_report')) ? $this->lang->line('label_create_report') : 'Create Report'; ?></i>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="row">

                            <div class='col-md-12'>
                                <div class="card">

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <select id="status" name="status" class="form-control">
                                                    <option value=""><?= !empty($this->lang->line('label_all')) ? $this->lang->line('label_all') : 'All'; ?></option>
                                                    <option value="notstarted"><?= !empty($this->lang->line('label_notstarted')) ? $this->lang->line('label_notstarted') : 'Not Started'; ?></option>
                                                    <option value="ongoing"><?= !empty($this->lang->line('label_ongoing')) ? $this->lang->line('label_ongoing') : 'Ongoing'; ?></option>
                                                    <option value="finished"><?= !empty($this->lang->line('label_finished')) ? $this->lang->line('label_finished') : 'Finished'; ?></option>
                                                    <option value="onhold"><?= !empty($this->lang->line('label_onhold')) ? $this->lang->line('label_onhold') : 'OnHold'; ?></option>
                                                    <option value="cancelled"><?= !empty($this->lang->line('label_cancelled')) ? $this->lang->line('label_cancelled') : 'Cancelled'; ?></option>
                                                </select>
                                            </div>
                                            <?php if (!is_client()) { ?>
                                                <div class="form-group col-md-3">
                                                    <select id="client_id" name="client_id" class="form-control">
                                                        <option value=""><?= !empty($this->lang->line('label_select_clients')) ? $this->lang->line('label_select_clients') : 'Select Clients'; ?></option>
                                                        <?php foreach ($all_user as $all_users) {
                                                            if (is_client($all_users->id)) { ?>
                                                                <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                                                        <?php }
                                                        } ?>
                                                    </select>
                                                </div>

                                                <div class="form-group col-md-3">
                                                    <select class="form-control" name="user_id" id="user_id">
                                                        <option value=""><?= !empty($this->lang->line('label_select_users')) ? $this->lang->line('label_select_users') : 'Select Users'; ?></option>
                                                        <?php foreach ($all_user as $all_users) {
                                                            if (!is_client($all_users->id)) { ?>
                                                                <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                                                        <?php }
                                                        } ?>
                                                    </select>
                                                </div>
                                            <?php } ?>
                                            <div class="form-group col-md-2">
                                                <i class="btn btn-primary btn-rounded no-shadow" id="fillter">Filtter</i>
                                            </div>
                                        </div>


                                        <table class='table-striped' id='report_list' data-toggle="table" data-url="<?= base_url('reporting/get_reports_list') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="true" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{
                      "fileName": "report-list",
                      "ignoreColumn": ["state"] 
                    }' data-query-params="queryParams">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" data-visible="false" data-sortable="true"><?= !empty($this->lang->line('label_id')) ? $this->lang->line('label_id') : 'ID'; ?></th>


                                                    <th data-field="title" data-sortable="true"><?= !empty($this->lang->line('label_title')) ? $this->lang->line('label_title') : 'Title'; ?></th>

                                                    <th data-field="description" data-visible="false" data-sortable="true"><?= !empty($this->lang->line('label_description')) ? $this->lang->line('label_description') : 'Description'; ?></th>

                                                    <th data-field="report_type" data-sortable="true"><?= !empty($this->lang->line('label_report_type')) ? $this->lang->line('label_report_type') : 'Report Type'; ?></th>

                                                    <th data-field="start_date" data-visible="false" data-sortable="true"><?= !empty($this->lang->line('label_start_date')) ? $this->lang->line('label_start_date') : 'Start Date'; ?></th>

                                                    <th data-field="end_date" data-visible="false" data-sortable="true"><?= !empty($this->lang->line('label_end_date')) ? $this->lang->line('label_end_date') : 'End Date'; ?></th>

                                                    <th data-field="reports_userss" data-sortable="false"><?= !empty($this->lang->line('label_report_to')) ? $this->lang->line('label_report_to') : 'Report To'; ?></th>

                                                    <th data-field="reports_clientss" data-visible="false" data-sortable="false"><?= !empty($this->lang->line('label_clients')) ? $this->lang->line('label_clients') : 'Clients'; ?></th>

                                                    <th data-field="status" data-sortable="true"><?= !empty($this->lang->line('label_status')) ? $this->lang->line('label_status') : 'Status'; ?></th>

                                                    <th data-field="action" data-sortable="false"><?= !empty($this->lang->line('label_action')) ? $this->lang->line('label_action') : 'Action'; ?></th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </section>
            </div>

            <form action="<?= base_url('reporting/create'); ?>" method="post" class="modal-part" id="modal-add-report-part">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_title')) ? $this->lang->line('label_title') : 'Title'; ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="title" name="title">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="report_type"><?= !empty($this->lang->line('label_report_type')) ? $this->lang->line('label_report_type') : 'Report Type'; ?></label>
                                <select id="report_type" name="report_type" class="form-control">
                                    <option> - - </option>
                                    <option value="<?= !empty($this->lang->line('label_general')) ? $this->lang->line('label_general') : 'General'; ?>"><?= !empty($this->lang->line('label_general')) ? $this->lang->line('label_general') : 'General'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_daily_report')) ? $this->lang->line('label_daily_report') : 'Daily Report'; ?>"><?= !empty($this->lang->line('label_daily_report')) ? $this->lang->line('label_daily_report') : 'Daily Report'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_monthly_report')) ? $this->lang->line('label_monthly_report') : 'Monthly Report'; ?>"><?= !empty($this->lang->line('label_monthly_report')) ? $this->lang->line('label_monthly_report') : 'Monthly Report'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_quartely_report')) ? $this->lang->line('label_quartely_report') : 'Quaterly Report'; ?>"><?= !empty($this->lang->line('label_quartely_report')) ? $this->lang->line('label_quartely_report') : 'Quaterly Report'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_yearly_report')) ? $this->lang->line('label_yearly_report') : 'Yearly Report'; ?>"><?= !empty($this->lang->line('label_yearly_report')) ? $this->lang->line('label_yearly_report') : 'Yearly Report'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_problems_report')) ? $this->lang->line('label_problems_report') : 'Problems Report'; ?>"><?= !empty($this->lang->line('label_problems_report')) ? $this->lang->line('label_problems_report') : 'Problems Report'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_achievements_report')) ? $this->lang->line('label_achievements_report') : 'Achievements Report'; ?>"><?= !empty($this->lang->line('label_achievements_report')) ? $this->lang->line('label_achievements_report') : 'Achievements Report'; ?></option>
                                </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status"><?= !empty($this->lang->line('label_status')) ? $this->lang->line('label_status') : 'Status'; ?></label>
                                    <select id="status" name="status" class="form-control">
                                        <option> -- </option>
                                        <option value="notstarted"><?= !empty($this->lang->line('label_notstarted')) ? $this->lang->line('label_notstarted') : 'Not Started'; ?></option>
                                        <option value="ongoing"><?= !empty($this->lang->line('label_ongoing')) ? $this->lang->line('label_ongoing') : 'Ongoing'; ?></option>
                                        <option value="finished"><?= !empty($this->lang->line('label_finished')) ? $this->lang->line('label_finished') : 'Finished'; ?></option>
                                        <option value="onhold"><?= !empty($this->lang->line('label_onhold')) ? $this->lang->line('label_onhold') : 'OnHold'; ?></option>
                                        <option value="cancelled"><?= !empty($this->lang->line('label_cancelled')) ? $this->lang->line('label_cancelled') : 'Cancelled'; ?></option>
                                    </select>
                                </div>
                            </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date"><?= !empty($this->lang->line('label_end_date')) ? $this->lang->line('label_end_date') : 'End Date'; ?></label>
                            <input class="form-control datepicker" type="text" id="end_date" name="end_date" value="" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date"><?= !empty($this->lang->line('label_start_date')) ? $this->lang->line('label_start_date') : 'Start Date'; ?></label>
                            <input class="form-control datepicker" type="text" id="start_date" name="start_date" value="" autocomplete="off">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_description')) ? $this->lang->line('label_description') : 'Description'; ?></label>
                            <div class="input-group">
                                <textarea id="description" class="form-control" name="description"></textarea>
                            </div>
                        </div>
                    </div>
                    <?php if (!is_client()) { ?>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?= !empty($this->lang->line('label_select_users')) ? $this->lang->line('label_select_users') : 'Select Users'; ?></label>
                                <select class="form-control select2" multiple="" name="users[]" id="users">
                                    <?php foreach ($all_user as $all_users) {
                                        if (!is_client($all_users->id)) { ?>
                                            <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="clients"><?= !empty($this->lang->line('label_select_clients')) ? $this->lang->line('label_select_clients') : 'Select Clients'; ?></label>
                                <select id="clients" name="clients[]" class="form-control select2" multiple="">
                                    <?php foreach ($all_user as $all_users) {
                                        if (is_client($all_users->id)) { ?>
                                            <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                        </div>
                    <?php } ?>

                </div>
            </form>

            <div class="modal-edit-report"></div>
            <form action="<?= base_url('reporting/edit'); ?>" method="post" class="modal-part" id="modal-edit-report-part">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_title')) ? $this->lang->line('label_title') : 'Title'; ?></label>
                            <div class="input-group">
                                <input type="hidden" name="update_id" id="update_id">
                                <input type="text" class="form-control" placeholder="title" name="title" id="update_title">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status"><?= !empty($this->lang->line('label_status')) ? $this->lang->line('label_status') : 'Status'; ?></label>
                                    <select name="status" class="form-control" id="update_status">
                                        <option value="notstarted"><?= !empty($this->lang->line('label_notstarted')) ? $this->lang->line('label_notstarted') : 'Not Started'; ?></option>
                                        <option value="ongoing"><?= !empty($this->lang->line('label_ongoing')) ? $this->lang->line('label_ongoing') : 'Ongoing'; ?></option>
                                        <option value="finished"><?= !empty($this->lang->line('label_finished')) ? $this->lang->line('label_finished') : 'Finished'; ?></option>
                                        <option value="onhold"><?= !empty($this->lang->line('label_onhold')) ? $this->lang->line('label_onhold') : 'OnHold'; ?></option>
                                        <option value="cancelled"><?= !empty($this->lang->line('label_cancelled')) ? $this->lang->line('label_cancelled') : 'Cancelled'; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="report_type"><?= !empty($this->lang->line('report_type')) ? $this->lang->line('report_type') : 'Report Type'; ?></label>
                                    <select name="report_type" class="form-control" id="update_report_type">
                                        <option value="General"><?= !empty($this->lang->line('label_general')) ? $this->lang->line('label_general') : 'General'; ?></option>
                                        <option value="<?= !empty($this->lang->line('label_daily_report')) ? $this->lang->line('label_daily_report') : 'Daily Report'; ?>"><?= !empty($this->lang->line('label_daily_report')) ? $this->lang->line('label_daily_report') : 'Daily Report'; ?></option>
                                        <option value="<?= !empty($this->lang->line('label_monthly_report')) ? $this->lang->line('label_monthly_report') : 'Monthly Report'; ?>"><?= !empty($this->lang->line('label_monthly_report')) ? $this->lang->line('label_monthly_report') : 'Monthly Report'; ?></option>
                                        <option value="<?= !empty($this->lang->line('label_quartely_report')) ? $this->lang->line('label_quartely_report') : 'Quaterly Report'; ?>"><?= !empty($this->lang->line('label_quartely_report')) ? $this->lang->line('label_quartely_report') : 'Quaterly Report'; ?></option>
                                        <option value="<?= !empty($this->lang->line('label_yearly_report')) ? $this->lang->line('label_yearly_report') : 'Yearly Report'; ?>"><?= !empty($this->lang->line('label_yearly_report')) ? $this->lang->line('label_yearly_report') : 'Yearly Report'; ?></option>
                                        <option value="<?= !empty($this->lang->line('label_problems_report')) ? $this->lang->line('label_problems_report') : 'Problems Report'; ?>"><?= !empty($this->lang->line('label_problems_report')) ? $this->lang->line('label_problems_report') : 'Problems Report'; ?></option>
                                        <option value="<?= !empty($this->lang->line('label_achievements_report')) ? $this->lang->line('label_achievements_report') : 'Achievements Report'; ?>"><?= !empty($this->lang->line('label_achievements_report')) ? $this->lang->line('label_achievements_report') : 'Achievements Report'; ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date"><?= !empty($this->lang->line('label_start_date')) ? $this->lang->line('label_start_date') : 'Start Date'; ?></label>
                            <input class="form-control datepicker" type="text" id="update_start_date" name="start_date" value="2019-07-24" autocomplete="off">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date"><?= !empty($this->lang->line('label_end_date')) ? $this->lang->line('label_end_date') : 'End Date'; ?></label>
                            <input class="form-control datepicker" type="text" id="update_end_date" name="end_date" value="2019-07-30" autocomplete="off">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_description')) ? $this->lang->line('label_description') : 'Description'; ?></label>
                            <div class="input-group">
                                <textarea type="textarea" class="form-control" placeholder="description" name="description" id="update_description"></textarea>
                            </div>
                        </div>
                    </div>
                    <?php if (!is_client()) { ?>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?= !empty($this->lang->line('label_select_users')) ? $this->lang->line('label_select_users') : 'Select Users'; ?> (Make Sure You Don't Remove Yourself From report)</label>
                                <select class="form-control select2" multiple="" name="users[]" id="update_users">
                                    <?php foreach ($all_user as $all_users) { ?>
                                        <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="clients"><?= !empty($this->lang->line('label_select_clients')) ? $this->lang->line('label_select_clients') : 'Select Clients'; ?></label>
                                <select id="update_clients" name="clients[]" class="form-control select2" multiple="">
                                    <?php foreach ($all_user as $all_users) {
                                        if (is_client($all_users->id)) { ?>
                                            <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                                    <?php }
                                    } ?>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </form>

            <?php include('include-footer.php'); ?>

        </div>
    </div>

    <?php include('include-js.php'); ?>
    <!-- include summernote css/js -->
    <!-- Page Specific JS File -->
    <script src="<?= base_url('assets/js/page/components-user.js'); ?>"></script>
    <script type="text/javascript">
        $(document).ready(function() {
        $('#description').summernote();
        });
    </script>
</body>

</html>