<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>Report Details &mdash; <?= get_compnay_title(); ?></title>
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
                        <h1><?= !empty($this->lang->line('label_report_details')) ? $this->lang->line('label_report_details') : 'Report Details'; ?></h1>
                    </div>

                    <div class="section-body">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="card author-box card-<?= $reports['class']; ?>">
                                    <div class="card-body">
                                        <div class="author-box-name">
                                            <h4><?= $reports['title']; ?></h4>
                                            <h6>Report Type: <?= $reports['report_type']; ?></h6>
                                        </div>
                                        <div class="author-box-job">
                                            <div class="badge badge-<?= $reports['class']; ?> reports-badge"><?= $reports['status']; ?></div>
                                            <div class="float-right mt-sm-1">
                                                <b><?= !empty($this->lang->line('label_start_date')) ? $this->lang->line('label_start_date') : 'Start Date'; ?>: </b><?= $reports['start_date']; ?> <b><?= !empty($this->lang->line('label_end_date')) ? $this->lang->line('label_end_date') : 'End Date'; ?>: </b><?= $reports['end_date']; ?>
                                            </div>
                                        </div>
                                        <div class="author-box-description">
                                            <p><?= $reports['description']; ?></p>
                                        </div>

                                        <div class="row">
                                            <?php if (!empty($reports['reports_clients'])) { ?>
                                                <div class="col-md-6">
                                                    <h6><?= !empty($this->lang->line('label_clients')) ? $this->lang->line('label_clients') : 'Clients'; ?></h6>
                                                    <?php foreach ($reports['reports_clients'] as $reports_clients) {

                                                        if (isset($reports_clients['profile']) && !empty($reports_clients['profile'])) { ?>
                                                            <a href="<?= base_url('users/detail/' . $reports_clients['id']) ?>">
                                                                <figure class="avatar avatar-md" data-toggle="tooltip" data-title="<?= $reports_clients['first_name'] ?>">
                                                                    <img alt="image" src="<?= base_url('assets/profiles/' . $reports_clients['profile']); ?>" class="rounded-circle">
                                                                </figure>
                                                            </a>
                                                        <?php } else { ?>
                                                            <a href="<?= base_url('users/detail/' . $reports_clients['id']) ?>">
                                                                <figure data-toggle="tooltip" data-title="<?= $reports_clients['first_name'] ?>" class="avatar avatar-md" data-initial="<?= mb_substr($reports_clients['first_name'], 0, 1) . '' . mb_substr($reports_clients['last_name'], 0, 1); ?>">
                                                                </figure>
                                                            </a>
                                                    <?php }
                                                    } ?>
                                                </div>
                                            <?php } ?>
                                            <div class="col-md-6">
                                                <h6 class="mt-1"><?= !empty($this->lang->line('label_users')) ? $this->lang->line('label_users') : 'Users'; ?></h6>
                                                <?php foreach ($reports['reports_users'] as $reports_users) {
                                                    if (isset($reports_users['profile']) && !empty($reports_users['profile'])) { ?>
                                                        <a href="<?= base_url('users/detail/' . $reports_users['id']) ?>">
                                                            <figure class="avatar avatar-md" data-toggle="tooltip" data-title="<?= $reports_users['first_name'] ?>">
                                                                <img alt="image" src="<?= base_url('assets/profiles/' . $reports_users['profile']); ?>" class="rounded-circle">
                                                            </figure>
                                                        </a>
                                                    <?php } else { ?>
                                                        <a href="<?= base_url('users/detail/' . $reports_users['id']) ?>">
                                                            <figure data-toggle="tooltip" data-title="<?= $reports_users['first_name'] ?>" class="avatar avatar-md" data-initial="<?= mb_substr($reports_users['first_name'], 0, 1) . '' . mb_substr($reports_users['last_name'], 0, 1); ?>">
                                                            </figure>
                                                        </a>
                                                <?php }
                                                } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card author-box">
                                    <div class="card-body">
                                        <div class="author-box-name mb-4">
                                            <?= !empty($this->lang->line('label_upload_files')) ? $this->lang->line('label_upload_files') : 'Upload Files'; ?>
                                        </div>
                                        <input type="hidden" id="workspace_id" value="<?= $reports['workspace_id'] ?>">
                                        <input type="hidden" id="report_id" value="<?= $reports['id'] ?>">
                                        <div class="dropzone dz-clickable" id="report-files-dropzone">
                                            <div class="dz-default dz-message">
                                                <span>
                                                    <?= !empty($this->lang->line('label_drop_files_here_to_upload')) ? $this->lang->line('label_drop_files_here_to_upload') : 'Drop files here to upload'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>
                                            <?= !empty($this->lang->line('label_uploaded_files')) ? $this->lang->line('label_uploaded_files') : 'Uploaded Files'; ?>
                                        </h4>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive table-invoice">
                                            <table class="table table-striped">
                                                <tr>
                                                    <th><?= !empty($this->lang->line('label_preview')) ? $this->lang->line('label_preview') : 'Preview'; ?></th>
                                                    <th><?= !empty($this->lang->line('label_name')) ? $this->lang->line('label_name') : 'Name'; ?></th>
                                                    <th><?= !empty($this->lang->line('label_size')) ? $this->lang->line('label_size') : 'Size'; ?></th>
                                                    <th><?= !empty($this->lang->line('label_action')) ? $this->lang->line('label_action') : 'Action'; ?></th>
                                                </tr>
                                                <?php if (empty($files)) { ?>
                                                    <tr class="text-center">
                                                        <td colspan="4"><b>No files uploaded yet!</b></td>
                                                    </tr>
                                                    <?php } else {
                                                    foreach ($files as $file) { ?>
                                                        <tr>
                                                            <td><?= $file['file_extension'] ?></td>
                                                            <td><?= $file['original_file_name'] ?></td>
                                                            <td class="font-weight-600"><?= $file['file_size'] ?></td>
                                                            <td>
                                                                <a download="<?= $file['original_file_name'] ?>" href="<?= base_url('assets/report/' . $file['file_name']); ?>" class="btn btn-primary btn-action mt-1 "><i class="fas fa-download"></i></a>
                                                                <a class="btn btn-danger btn-action mt-1 delete-report-file-alert" data-file_id="<?= $file['id'] ?>"><i class="fas fa-trash"></i></a>
                                                            </td>
                                                        </tr>
                                                <?php }
                                                } ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>


                    </div>
                </section>
            </div>



            <?php include('include-footer.php'); ?>

        </div>
    </div>

    <script>
        label_todo = "<?= !empty($this->lang->line('label_todo')) ? $this->lang->line('label_todo') : 'Todo'; ?>";
        label_in_progress = "<?= !empty($this->lang->line('label_inprogress')) ? $this->lang->line('label_inprogress') : 'In Progress'; ?>";
        label_review = "<?= !empty($this->lang->line('label_review')) ? $this->lang->line('label_review') : 'Review'; ?>";
        label_done = "<?= !empty($this->lang->line('label_done')) ? $this->lang->line('label_done') : 'Done'; ?>";
        label_tasks = "<?= !empty($this->lang->line('label_tasks')) ? $this->lang->line('label_tasks') : 'Tasks'; ?>";

        todo_task = "<?= $todo_task ?>";
        inprogress_task = "<?= $inprogress_task ?>";
        review_task = "<?= $review_task ?>";
        done_task = "<?= $done_task ?>";
        dictDefaultMessage = "<?= !empty($this->lang->line('label_drop_files_here_to_upload')) ? $this->lang->line('label_drop_files_here_to_upload') : 'Drop Files Here To Upload'; ?>";
    </script>

    <?php include('include-js.php'); ?>
    <!-- Page Specific JS File -->
    <script src="<?= base_url('assets/js/page/components-user.js'); ?>"></script>
    <script src="<?= base_url('assets/js/page/report-details.js'); ?>"></script>

</body>

</html>