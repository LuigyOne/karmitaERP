<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Projects &mdash; <?= get_compnay_title(); ?></title>
    <?php include('include-css.php'); ?>

</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">

            <?php include('include-header.php'); ?>

            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="section-header">
                        <h1><?= !empty($this->lang->line('label_projects')) ? $this->lang->line('label_projects') : 'Projects'; ?></h1>
                        <div class="section-header-breadcrumb">
                            <div class="btn-group mr-2 no-shadow">
                                <a href="<?= base_url('projects') ?>" class="btn"><i class="fas fa-th-large"></i> <?= !empty($this->lang->line('label_grid_view')) ? $this->lang->line('label_grid_view') : 'Grid View'; ?></a>
                                <a class="btn btn-primary text-white"><i class="fas fa-list"></i> <?= !empty($this->lang->line('label_list_view')) ? $this->lang->line('label_list_view') : 'List View'; ?></a>
                            </div>
                            <?php if (is_admin() || is_editor()) { ?>
                            <i class="btn btn-primary btn-rounded no-shadow" id="modal-add-project"><?= !empty($this->lang->line('label_create_project')) ? $this->lang->line('label_create_project') : 'Create Project'; ?></i>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="section-body">
                        <div class="row">




                            <div class='col-md-12'>
                                <div class="card">

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-md-3">
                                                <select id="projects_status" name="projects_status" class="form-control">
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
                                                <i class="btn btn-primary btn-rounded no-shadow" id="fillter"><?= !empty($this->lang->line('label_filter')) ? $this->lang->line('label_filter') : 'Filter'; ?></i>
                                            </div>
                                        </div>


                                        <table class='table-striped' id='projects_list' data-toggle="table" data-url="<?= base_url('projects/get_projects_list') ?>" data-click-to-select="true" data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true" data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true" data-toolbar="" data-show-export="true" data-maintain-selected="true" data-export-types='["txt","excel"]' data-export-options='{
                      "fileName": "projects-list",
                      "ignoreColumn": ["state"] 
                    }' data-query-params="queryParams">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" data-visible="false" data-sortable="true"><?= !empty($this->lang->line('label_id')) ? $this->lang->line('label_id') : 'ID'; ?></th>


                                                    <th data-field="title" data-sortable="true"><?= !empty($this->lang->line('label_projects')) ? $this->lang->line('label_projects') : 'Project'; ?></th>

                                                    <th data-field="meeting_lead" data-sortable="true"><?= !empty($this->lang->line('label_meeting_lead')) ? $this->lang->line('label_meeting_lead') : 'Ordered By'; ?></th>

                                                    <th data-field="section" data-sortable="true"><?= !empty($this->lang->line('label_section')) ? $this->lang->line('label_section') : 'Section'; ?></th>

                                                    <th data-field="project_type" data-sortable="true"><?= !empty($this->lang->line('label_project_type')) ? $this->lang->line('label_project_type') : 'Project Type'; ?></th>

                                                    <th data-field="description" data-visible="false" data-sortable="true"><?= !empty($this->lang->line('label_description')) ? $this->lang->line('label_description') : 'Description'; ?></th>

                                                    <th data-field="task_count" data-sortable="true"><?= !empty($this->lang->line('label_tasks')) ? $this->lang->line('label_tasks') : 'Tasks'; ?></th>

                                                    <th data-field="projects_userss" data-sortable="false"><?= !empty($this->lang->line('label_users')) ? $this->lang->line('label_users') : 'Users'; ?></th>

                                                    <th data-field="projects_clientss" data-sortable="false"><?= !empty($this->lang->line('label_clients')) ? $this->lang->line('label_clients') : 'Clients'; ?></th>

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

            <form action="<?= base_url('projects/create'); ?>" method="post" class="modal-part" id="modal-add-project-part">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_title')) ? $this->lang->line('label_title') : 'Title'; ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="title" name="title">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_project_number')) ? $this->lang->line('label_project_number') : 'Project ID'; ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Project/Meeting ID" name="project_number">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="section"><?= !empty($this->lang->line('label_section')) ? $this->lang->line('label_section') : 'Section'; ?></label>
                                <select id="section" name="section" class="form-control">
                                    <option value="">--</option>
                                    <option value="<?= !empty($this->lang->line('label_masawba')) ? $this->lang->line('label_masawba') : 'Masawba'; ?>"><?= !empty($this->lang->line('label_masawba')) ? $this->lang->line('label_masawba') : 'Masawba'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_hedayat')) ? $this->lang->line('label_hedayat') : 'Hedayat'; ?>"><?= !empty($this->lang->line('label_hedayat')) ? $this->lang->line('label_hedayat') : 'Hedayat'; ?></option>
                                </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_type"><?= !empty($this->lang->line('label_project_type')) ? $this->lang->line('label_project_type') : 'Project Type'; ?></label>
                                <select id="project_type" name="project_type" class="form-control">
                                    <option value="">---</option>
                                    <option value="<?= !empty($this->lang->line('label_general')) ? $this->lang->line('label_general') : 'General'; ?>"><?= !empty($this->lang->line('label_general')) ? $this->lang->line('label_general') : 'General'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_development')) ? $this->lang->line('label_development') : 'Development'; ?>"><?= !empty($this->lang->line('label_development')) ? $this->lang->line('label_development') : 'Development'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_construction')) ? $this->lang->line('label_construction') : 'Construction'; ?>"><?= !empty($this->lang->line('label_construction')) ? $this->lang->line('label_construction') : 'Construction'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_consultancy')) ? $this->lang->line('label_consultancy') : 'Consultancy'; ?>"><?= !empty($this->lang->line('label_consultancy')) ? $this->lang->line('label_consultancy') : 'Consultancy'; ?></option>
                                </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                            <div class="form-group">
                                <label for="meeting_lead"><?= !empty($this->lang->line('label_meeting_lead')) ? $this->lang->line('label_meeting_lead') : 'Ordered By'; ?></label>
                                <select id="meeting_lead" name="meeting_lead" class="form-control">
                                    
                                    <option value=""> --- </option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_minister')) ? $this->lang->line('label_minister') : 'Minister'; ?>"><?= !empty($this->lang->line('label_minister')) ? $this->lang->line('label_minister') : 'Minister'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_president')) ? $this->lang->line('label_president') : 'President of The Islamic Republic of Afghanistan'; ?>"><?= !empty($this->lang->line('label_president')) ? $this->lang->line('label_president') : 'President of The Islamic Republic of Afghanistan'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_cabinet')) ? $this->lang->line('label_cabinet') : 'Cabinet'; ?>"><?= !empty($this->lang->line('label_cabinet')) ? $this->lang->line('label_cabinet') : 'Cabinet'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_council_of_ministers')) ? $this->lang->line('label_council_of_ministers') : 'The Council of Ministers'; ?>"><?= !empty($this->lang->line('label_council_of_ministers')) ? $this->lang->line('label_council_of_ministers') : 'The Council of Ministers'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_supreme_council_of_the_law')) ? $this->lang->line('label_supreme_council_of_the_law') : 'Supreme Council of the Rule of Law'; ?>"><?= !empty($this->lang->line('label_supreme_council_of_the_law')) ? $this->lang->line('label_supreme_council_of_the_law') : 'Supreme Council of the Rule of Law'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_supreme_economic_council')) ? $this->lang->line('label_supreme_economic_council') : 'Supreme Economic Council'; ?>"><?= !empty($this->lang->line('label_supreme_economic_council')) ? $this->lang->line('label_supreme_economic_council') : 'Supreme Economic Council'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_high_council_of_regional_connection')) ? $this->lang->line('label_high_council_of_regional_connection') : 'High Council of Regional Connection'; ?>"><?= !empty($this->lang->line('label_high_council_of_regional_connection')) ? $this->lang->line('label_high_council_of_regional_connection') : 'High Council of Regional Connection'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_national_procurement_commission')) ? $this->lang->line('label_national_procurement_commission') : 'National Procurement Commission'; ?>"><?= !empty($this->lang->line('label_national_procurement_commission')) ? $this->lang->line('label_national_procurement_commission') : 'National Procurement Commission'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_high_council_of_infrastructure')) ? $this->lang->line('label_high_council_of_infrastructure') : 'High Council of Infrastructure'; ?>"><?= !empty($this->lang->line('label_high_council_of_infrastructure')) ? $this->lang->line('label_high_council_of_infrastructure') : 'High Council of Infrastructure'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_high_council_for_poverty_reduction')) ? $this->lang->line('label_high_council_for_poverty_reduction') : 'High Council for Poverty Reduction'; ?>"><?= !empty($this->lang->line('label_high_council_for_poverty_reduction')) ? $this->lang->line('label_high_council_for_poverty_reduction') : 'High Council for Poverty Reduction'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_scientific_cultural_and_human_resources_council_office_of_affairs')) ? $this->lang->line('label_scientific_cultural_and_human_resources_council_office_of_affairs') : 'Scientific, Cultural and Human Resources Council, Office of Affairs'; ?>"><?= !empty($this->lang->line('label_scientific_cultural_and_human_resources_council_office_of_affairs')) ? $this->lang->line('label_scientific_cultural_and_human_resources_council_office_of_affairs') : 'Scientific, Cultural and Human Resources Council, Office of Affairs'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_directorate_of_national_security')) ? $this->lang->line('label_directorate_of_national_security') : 'Directorate of National Security'; ?>"><?= !empty($this->lang->line('label_directorate_of_national_security')) ? $this->lang->line('label_directorate_of_national_security') : 'Directorate of National Security'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_finance')) ? $this->lang->line('label_ministry_of_finance') : 'Ministry of Finance'; ?>"><?= !empty($this->lang->line('label_ministry_of_finance')) ? $this->lang->line('label_ministry_of_finance') : 'Ministry of Finance'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_interior')) ? $this->lang->line('label_ministry_of_interior') : 'Ministry of Interior'; ?>"><?= !empty($this->lang->line('label_ministry_of_interior')) ? $this->lang->line('label_ministry_of_interior') : 'Ministry of Interior'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_mines_and_petroleum')) ? $this->lang->line('label_ministry_of_mines_and_petroleum') : 'Ministry of Mines and Petroleum'; ?>"><?= !empty($this->lang->line('label_ministry_of_mines_and_petroleum')) ? $this->lang->line('label_ministry_of_mines_and_petroleum') : 'Ministry of Mines and Petroleum'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_economy')) ? $this->lang->line('label_ministry_of_economy') : 'Ministry of Economy'; ?>"><?= !empty($this->lang->line('label_ministry_of_economy')) ? $this->lang->line('label_ministry_of_economy') : 'Ministry of Economy'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_foreign_affairs')) ? $this->lang->line('label_ministry_of_foreign_affairs') : 'Ministry of Foreign Affairs'; ?>"><?= !empty($this->lang->line('label_ministry_of_foreign_affairs')) ? $this->lang->line('label_ministry_of_foreign_affairs') : 'Ministry of Foreign Affairs'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_rural_rehabilitation_and_development')) ? $this->lang->line('label_ministry_of_rural_rehabilitation_and_development') : 'Ministry of Rural Rehabilitation and Development'; ?>"><?= !empty($this->lang->line('label_ministry_of_rural_rehabilitation_and_development')) ? $this->lang->line('label_ministry_of_rural_rehabilitation_and_development') : 'Ministry of Rural Rehabilitation and Development'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_agriculture_irrigation_and_livestock')) ? $this->lang->line('label_ministry_of_agriculture_irrigation_and_livestock') : 'Ministry of Agriculture, Irrigation and Livestock'; ?>"><?= !empty($this->lang->line('label_ministry_of_agriculture_irrigation_and_livestock')) ? $this->lang->line('label_ministry_of_agriculture_irrigation_and_livestock') : 'Ministry of Agriculture, Irrigation and Livestock'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_councilor_of_the_ministry_of_finance')) ? $this->lang->line('label_councilor_of_the_ministry_of_finance') : 'Councilor of the Ministry of Finance'; ?>"><?= !empty($this->lang->line('label_councilor_of_the_ministry_of_finance')) ? $this->lang->line('label_councilor_of_the_ministry_of_finance') : 'Councilor of the Ministry of Finance'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_urban_development_and_housing')) ? $this->lang->line('label_ministry_of_urban_development_and_housing') : 'Ministry of Urban Development and Housing'; ?>"><?= !empty($this->lang->line('label_ministry_of_urban_development_and_housing')) ? $this->lang->line('label_ministry_of_urban_development_and_housing') : 'Ministry of Urban Development and Housing'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_commerce_and_industry')) ? $this->lang->line('label_ministry_of_commerce_and_industry') : 'Ministry of Commerce and Industry'; ?>"><?= !empty($this->lang->line('label_ministry_of_commerce_and_industry')) ? $this->lang->line('label_ministry_of_commerce_and_industry') : 'Ministry of Commerce and Industry'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_ministry_of_justice')) ? $this->lang->line('label_ministry_of_justice') : 'Ministry of Justice'; ?>"><?= !empty($this->lang->line('label_ministry_of_justice')) ? $this->lang->line('label_ministry_of_justice') : 'Ministry of Justice'; ?></option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_other')) ? $this->lang->line('label_other') : 'Other'; ?>"><?= !empty($this->lang->line('label_other')) ? $this->lang->line('label_other') : 'Other'; ?></option>
                                    
                                </select>
                        </div>
                    </div>
                    
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status"><?= !empty($this->lang->line('label_status')) ? $this->lang->line('label_status') : 'Status'; ?></label>
                                    <select id="status" name="status" class="form-control">
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
                                    <label for="budget"><?= !empty($this->lang->line('label_budget')) ? $this->lang->line('label_budget') : 'Budget'; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-addon bootstrap-touchspin-prefix input-group-prepend"><span class="input-group-text"><?= get_currency_symbol(); ?></span></span>
                                        <input class="form-control" type="number" min="0" id="budget" name="budget" value="0" placeholder="Project Budget">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date"><?= !empty($this->lang->line('label_start_date')) ? $this->lang->line('label_start_date') : 'Start Date'; ?></label>
                            <input class="form-control" type="text" id="start_date" name="start_date" value="" autocomplete="off">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date"><?= !empty($this->lang->line('label_end_date')) ? $this->lang->line('label_end_date') : 'End Date'; ?></label>
                            <input class="form-control datepicker" type="text" id="end_date" name="end_date" value="" autocomplete="off">
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
            <div class="modal-edit-project"></div>
            <form action="<?= base_url('projects/edit'); ?>" method="post" class="modal-part" id="modal-edit-project-part">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_title')) ? $this->lang->line('label_title') : 'Title'; ?></label>
                            <div class="input-group">
                                <input type="hidden" name="update_id" id="update_id">
                                <input type="text" class="form-control" placeholder="title" name="title" id="update_title">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_project_number')) ? $this->lang->line('label_project_number') : 'Project ID'; ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="project number" name="project_number" id="update_project_number">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="section"><?= !empty($this->lang->line('label_section')) ? $this->lang->line('label_section') : 'Section'; ?></label>
                                <select name="section" class="form-control" id="update_section">
                                    <option value=""> --- </option>
                                    <option value="<?= !empty($this->lang->line('label_masawba')) ? $this->lang->line('label_masawba') : 'Masawba'; ?>"><?= !empty($this->lang->line('label_masawba')) ? $this->lang->line('label_masawba') : 'Masawba'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_hedayat')) ? $this->lang->line('label_hedayat') : 'Hedayat'; ?>"><?= !empty($this->lang->line('label_hedayat')) ? $this->lang->line('label_hedayat') : 'Hedayat'; ?></option>
                                </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_type"><?= !empty($this->lang->line('label_project_type')) ? $this->lang->line('label_project_type') : 'Project Type'; ?></label>
                                <select name="project_type" class="form-control" id="update_project_type">
                                    <option value=""> --- </option>
                                    <option value="<?= !empty($this->lang->line('label_general')) ? $this->lang->line('label_general') : 'General'; ?>"><?= !empty($this->lang->line('label_general')) ? $this->lang->line('label_general') : 'General'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_development')) ? $this->lang->line('label_development') : 'Development'; ?>"><?= !empty($this->lang->line('label_development')) ? $this->lang->line('label_development') : 'Development'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_construction')) ? $this->lang->line('label_construction') : 'Construction'; ?>"><?= !empty($this->lang->line('label_construction')) ? $this->lang->line('label_construction') : 'Construction'; ?></option>
                                    <option value="<?= !empty($this->lang->line('label_consultancy')) ? $this->lang->line('label_consultancy') : 'Consultancy'; ?>"><?= !empty($this->lang->line('label_consultancy')) ? $this->lang->line('label_consultancy') : 'Consultancy'; ?></option>
                                </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                            <div class="form-group">
                                <label for="meeting_lead"><?= !empty($this->lang->line('label_meeting_lead')) ? $this->lang->line('label_meeting_lead') : 'Ordered By'; ?></label>
                                <select name="meeting_lead" class="form-control" id="update_meeting_lead">
                                    
                                    <option value=""> --- </option>
                                    
                                    <option value="<?= !empty($this->lang->line('label_minister')) ? $this->lang->line('label_minister') : 'Minister'; ?>"><?= !empty($this->lang->line('label_minister')) ? $this->lang->line('label_minister') : 'Minister'; ?></option>
                                    
                                    <option value="President of The Islamic Republic of Afghanistan"><?= !empty($this->lang->line('label_president')) ? $this->lang->line('label_president') : 'President of The Islamic Republic of Afghanistan'; ?></option>
                                    
                                    <option value="Cabinet"><?= !empty($this->lang->line('label_cabinet')) ? $this->lang->line('label_cabinet') : 'Cabinet'; ?></option>
                                    
                                    <option value="Council of Ministers"><?= !empty($this->lang->line('label_council_of_ministers')) ? $this->lang->line('label_council_of_ministers') : 'The Council of Ministers'; ?></option>
                                    
                                    <option value="Supreme Council of the Rule of Law"><?= !empty($this->lang->line('label_supreme_council_of_the_law')) ? $this->lang->line('label_supreme_council_of_the_law') : 'Supreme Council of the Rule of Law'; ?></option>
                                    
                                    <option value="Supreme Economic Council"><?= !empty($this->lang->line('label_supreme_economic_council')) ? $this->lang->line('label_supreme_economic_council') : 'Supreme Economic Council'; ?></option>
                                    
                                    <option value="High Council of Regional Connection"><?= !empty($this->lang->line('label_high_council_of_regional_connection')) ? $this->lang->line('label_high_council_of_regional_connection') : 'High Council of Regional Connection'; ?></option>
                                    
                                    <option value="National Procurement Commission"><?= !empty($this->lang->line('label_national_procurement_commission')) ? $this->lang->line('label_national_procurement_commission') : 'National Procurement Commission'; ?></option>
                                    
                                    <option value="High Council of Infrastructure"><?= !empty($this->lang->line('label_high_council_of_infrastructure')) ? $this->lang->line('label_high_council_of_infrastructure') : 'High Council of Infrastructure'; ?></option>
                                    
                                    <option value="High Council for Poverty Reduction"><?= !empty($this->lang->line('label_high_council_for_poverty_reduction')) ? $this->lang->line('label_high_council_for_poverty_reduction') : 'High Council for Poverty Reduction'; ?></option>
                                    
                                    <option value="Scientific, Cultural and Human Resources Council, Office of Affairs"><?= !empty($this->lang->line('label_scientific_cultural_and_human_resources_council_office_of_affairs')) ? $this->lang->line('label_scientific_cultural_and_human_resources_council_office_of_affairs') : 'Scientific, Cultural and Human Resources Council, Office of Affairs'; ?></option>
                                    
                                    <option value="Directorate of National Security"><?= !empty($this->lang->line('label_directorate_of_national_security')) ? $this->lang->line('label_directorate_of_national_security') : 'Directorate of National Security'; ?></option>
                                    
                                    <option value="Ministry of Finance"><?= !empty($this->lang->line('label_ministry_of_finance')) ? $this->lang->line('label_ministry_of_finance') : 'Ministry of Finance'; ?></option>
                                    
                                    <option value="Ministry of Interior"><?= !empty($this->lang->line('label_ministry_of_interior')) ? $this->lang->line('label_ministry_of_interior') : 'Ministry of Interior'; ?></option>
                                    
                                    <option value="Ministry of Mines and Petroleum"><?= !empty($this->lang->line('label_ministry_of_mines_and_petroleum')) ? $this->lang->line('label_ministry_of_mines_and_petroleum') : 'Ministry of Mines and Petroleum'; ?></option>
                                    
                                    <option value="Ministry of Economy"><?= !empty($this->lang->line('label_ministry_of_economy')) ? $this->lang->line('label_ministry_of_economy') : 'Ministry of Economy'; ?></option>
                                    
                                    <option value="Ministry of Foreign Affairs"><?= !empty($this->lang->line('label_ministry_of_foreign_affairs')) ? $this->lang->line('label_ministry_of_foreign_affairs') : 'Ministry of Foreign Affairs'; ?></option>
                                    
                                    <option value="Ministry of Rural Rehabilitation and Development"><?= !empty($this->lang->line('label_ministry_of_rural_rehabilitation_and_development')) ? $this->lang->line('label_ministry_of_rural_rehabilitation_and_development') : 'Ministry of Rural Rehabilitation and Development'; ?></option>
                                    
                                    <option value="Ministry of Agriculture, Irrigation and Livestock"><?= !empty($this->lang->line('label_ministry_of_agriculture_irrigation_and_livestock')) ? $this->lang->line('label_ministry_of_agriculture_irrigation_and_livestock') : 'Ministry of Agriculture, Irrigation and Livestock'; ?></option>
                                    
                                    <option value="Councilor of the Ministry of Finance"><?= !empty($this->lang->line('label_councilor_of_the_ministry_of_finance')) ? $this->lang->line('label_councilor_of_the_ministry_of_finance') : 'Councilor of the Ministry of Finance'; ?></option>
                                    
                                    <option value="Ministry of Urban Development and Housing"><?= !empty($this->lang->line('label_ministry_of_urban_development_and_housing')) ? $this->lang->line('label_ministry_of_urban_development_and_housing') : 'Ministry of Urban Development and Housing'; ?></option>
                                    
                                    <option value="Ministry of Commerce and Industry"><?= !empty($this->lang->line('label_ministry_of_commerce_and_industry')) ? $this->lang->line('label_ministry_of_commerce_and_industry') : 'Ministry of Commerce and Industry'; ?></option>
                                    
                                    <option value="Ministry of Justice"><?= !empty($this->lang->line('label_ministry_of_justice')) ? $this->lang->line('label_ministry_of_justice') : 'Ministry of Justice'; ?></option>
                                    
                                    <option value="Other"><?= !empty($this->lang->line('label_other')) ? $this->lang->line('label_other') : 'Other'; ?></option>
                                    
                                </select>
                        </div>
                    </div>
                    <div class="col-md-12">
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
                                    <label for="budget"><?= !empty($this->lang->line('label_budget')) ? $this->lang->line('label_budget') : 'Budget'; ?></label>
                                    <div class="input-group">
                                        <span class="input-group-addon bootstrap-touchspin-prefix input-group-prepend"><span class="input-group-text"><?= get_currency_symbol(); ?></span></span>
                                        <input class="form-control" type="number" min="0" id="update_budget" name="budget" value="0" placeholder="Project Budget">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date"><?= !empty($this->lang->line('label_end_date')) ? $this->lang->line('label_end_date') : 'End Date'; ?></label>
                            <input class="form-control datepicker" type="text" id="update_end_date" name="end_date" value="2019-07-30" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date"><?= !empty($this->lang->line('label_start_date')) ? $this->lang->line('label_start_date') : 'Start Date'; ?></label>
                            <input class="form-control datepicker" type="text" id="update_start_date" name="start_date" value="2019-07-24" autocomplete="off">
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

                    <div class="col-md-12">
                        <div class="form-group">
                            <label><?= !empty($this->lang->line('label_select_users')) ? $this->lang->line('label_select_users') : 'Select Users'; ?> (Make Sure You Don't Remove Yourself From Project)</label>
                            <select class="form-control select2" multiple="" name="users[]" id="update_users">
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
                            <select id="update_clients" name="clients[]" class="form-control select2" multiple="">
                                <?php foreach ($all_user as $all_users) {
                                    if (is_client($all_users->id)) { ?>
                                        <option value="<?= $all_users->id ?>"><?= $all_users->first_name ?> <?= $all_users->last_name ?></option>
                                <?php }
                                } ?>
                            </select>
                        </div>
                    </div>

                </div>
            </form>

            <?php include('include-footer.php'); ?>

        </div>
    </div>

    <?php include('include-js.php'); ?>
    <script type="text/javascript">
    $(function() {
        $("#start_date").persianDatepicker();       
    });
</script>

    <!-- Page Specific JS File -->

    <script src="<?= base_url('assets/js/page/components-user.js'); ?>"></script>

</body>

</html>			