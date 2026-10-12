<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reporting_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'file']);
    }

    function get_reports_list($workspace_id, $user_id)
    {

        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'ASC';
        $where = '';
        $get = $this->input->get();
        if (isset($get['sort']))
            $sort = strip_tags($get['sort']);
        if (isset($get['offset']))
            $offset = strip_tags($get['offset']);
        if (isset($get['limit']))
            $limit = strip_tags($get['limit']);
        if (isset($get['order']))
            $order = strip_tags($get['order']);
        if (isset($get['search']) &&  !empty($get['search'])) {
            $search = strip_tags($get['search']);
            $where = " and (id like '%" . $search . "%' OR title like '%" . $search . "%' OR description like '%" . $search . "%' OR reports_userss like '%" . $search .  "%' OR status like '%" . $search . "%' OR report_type like '%" . $search . "%')";
        }

        if (isset($get['status']) && !empty($get['status'])) {
            $status = strip_tags($get['status']);
            $where .= " and status='" . $status . "'";
        }

        if (isset($get['user_id']) && !empty($get['user_id']) && empty($get['client_id'])) {
            $user_id = strip_tags($get['user_id']);
        }

        if (isset($get['client_id']) && !empty($get['client_id']) && empty($get['user_id'])) {
            $user_id = strip_tags($get['client_id']);
        }

        if (isset($get['client_id']) && !empty($get['client_id']) && isset($get['user_id']) && !empty($get['user_id'])) {
            $user_id = strip_tags($get['user_id']);
            $client_id = strip_tags($get['client_id']);
            if (!is_client($user_id)) {
                $where .= " AND FIND_IN_SET($client_id,client_id)";
            } else {
                $where .= " AND FIND_IN_SET($user_id,user_id)";
            }
        }

        if (!is_client($user_id)) {
            $query = $this->db->query("SELECT COUNT(id) as total FROM reporting WHERE FIND_IN_SET($user_id,user_id) AND workspace_id=$workspace_id " . $where);
        } else {
            $query = $this->db->query("SELECT COUNT(id) as total FROM reporting WHERE FIND_IN_SET($user_id,client_id) AND workspace_id=$workspace_id " . $where);
        }

        $res = $query->result_array();
        foreach ($res as $row) {
            $total = $row['total'];
        }

        if (!is_client($user_id)) {
            $query = $this->db->query("SELECT * FROM reporting WHERE FIND_IN_SET($user_id,user_id) AND workspace_id= $workspace_id " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit);
        } else {
            $query = $this->db->query("SELECT * FROM reporting WHERE FIND_IN_SET($user_id,client_id) AND workspace_id= $workspace_id " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit);
        }
        // print_r($this->db->last_query());    
        $res = $query->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        $this->config->load('taskhub');

        foreach ($res as $row) {
            $profile = '';
            $cprofile = '';
            $tempRow['id'] = $row['id'];

            $reports_user_ids = explode(',', $row['user_id']);
            $reports_userss = $this->users_model->get_user_array_responce($reports_user_ids);
            $i = 0;
            $j = count($reports_userss);
            foreach ($reports_userss as $reports_users) {
                if ($i < 2) {
                    if (isset($reports_users['profile']) && !empty($reports_users['profile'])) {
                        $profile .= '<a href="' . base_url('assets/profiles/' . $reports_users['profile']) . '" data-lightbox="images" data-title="' . $reports_users['first_name'] . '">
                        <img alt="image" class="mr-1 rounded-circle" width="30" src="' . base_url('assets/profiles/' . $reports_users['profile']) . '">
                        </a>';
                    } else {
                        $profile .= '<figure class="avatar mr-1 avatar-sm" data-toggle="tooltip" data-title="' . $reports_users['first_name'] . '" data-initial="' . mb_substr($reports_users['first_name'], 0, 1) . '' . mb_substr($reports_users['last_name'], 0, 1) . '">
                </figure>';
                    }
                    $j--;
                }
                $i++;
            }

            if ($i > 2) {
                $profile .= '<figure class="avatar mr-1 avatar-sm" data-toggle="tooltip" data-title="+' . $j . '" data-initial="+' . $j . '"> </figure>';
            }

            if (!empty($profile)) {
                $profiles = '<li class="media">
                    ' . $profile . '
                    </li>';
            } else {
                $profiles = 'Not assigned.';
            }

            $reports_client_ids = explode(',', $row['client_id']);
            $reports_clients = $this->users_model->get_user_array_responce($reports_client_ids);

            $ci = 0;
            $cj = count($reports_clients);
            foreach ($reports_clients as $reports_client) {
                if ($ci < 2) {
                    if (isset($reports_client['profile']) && !empty($reports_client['profile'])) {

                        $cprofile .= '<a href="' . base_url('assets/profiles/' . $reports_client['profile']) . '" data-lightbox="images" data-title="' . $reports_client['first_name'] . '">
                        <img alt="image" class="mr-3 rounded-circle" width="50" src="' . base_url('assets/profiles/' . $reports_client['profile']) . '">
                        </a>';
                    } else {
                        $cprofile .= '<figure class="avatar mr-1 avatar-sm" data-toggle="tooltip" data-title="' . $reports_client['first_name'] . '" data-initial="' . mb_substr($reports_client['first_name'], 0, 1) . '' . mb_substr($reports_client['last_name'], 0, 1) . '">
                </figure>';
                    }
                    $cj--;
                }
                $ci++;
            }

            if ($ci > 2) {
                $cprofile .= '<figure class="avatar mr-1 avatar-sm" data-toggle="tooltip" data-title="+' . $j . '" data-initial="+' . $j . '"> </figure>';
            }

            if (!empty($cprofile)) {
                $cprofiles = '<li class="media">
                    ' . $cprofile . '
                    </li>';
            } else {
                $cprofiles = 'Not assigned';
            }

            $action = '<a href="' . base_url('reporting/details/' . $row['id']) . '" class="btn btn-light mr-2"><i class="fas fa-eye"></i></a>';

            if (!is_client()) {
                $action .= '<div class="dropdown card-widgets">
                                <a href="#" class="btn btn-light" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item has-icon modal-edit-report-ajax" href="#" data-id="' . $row['id'] . '"><i class="fas fa-pencil-alt"></i>' . (!empty($this->lang->line('label_edit')) ? $this->lang->line('label_edit') : 'Edit') . '</a>
                                    <a class="dropdown-item has-icon delete-report-alert" href="' . base_url('reporting/delete/' . $row['id']) . '" data-report_id="' . $row['id'] . '"><i class="far fa-trash-alt"></i>' . (!empty($this->lang->line('label_delete')) ? $this->lang->line('label_delete') : 'Delete') . '</a>
                                </div>
                            </div>';
            }

            $action_btns = '<div class="btn-group no-shadow">
                        ' . $action . '
                        </div>';
            $tempRow['reports_userss'] = $profiles;
            $tempRow['reports_clientss'] = $cprofiles;
            $tempRow['title'] = $row['title'];
            $tempRow['report_type'] = $row['report_type'];
            $tempRow['start_date'] = $row['start_date'];
            $tempRow['end_date'] = $row['end_date'];
            $tempRow['description'] = mb_substr($row['description'], 0, 20) . '...';
            $report_status = !empty($this->lang->line('label_' . $row['status'])) ? $this->lang->line('label_' . $row['status']) : $row['status'];
            $tempRow['status'] = '<div class="badge badge-' . $row["class"] . ' reports-badge">' . $report_status . '</div>';

            $tempRow['action'] = $action_btns;

            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function create_report($data)
    {
        if ($this->db->insert('reporting', $data))
            return $this->db->insert_id();
        else
            return false;
    }


    function get_report($workspace_id, $user_id, $filter = '', $user_type = 'normal', $limit = '', $start = '')
    {

        if (!empty($limit)) {
            $where_limit = ' LIMIT ' . $limit;
            if (!empty($start)) {
                $where_limit .= ' OFFSET ' . $start;
            }
        } else {
            $where_limit = '';
        }

        if (!empty($filter)) {
            $where = "AND status='$filter'";
        } else {
            $where = '';
        }
        if ($user_type != 'normal') {
            $query = $this->db->query('SELECT * FROM reporting WHERE FIND_IN_SET(' . $user_id . ',`client_id`) AND workspace_id=' . $workspace_id . ' ' . $where . 'ORDER BY id desc ' . $where_limit);
        } else {
            $query = $this->db->query('SELECT * FROM reporting WHERE FIND_IN_SET(' . $user_id . ',`user_id`) AND workspace_id=' . $workspace_id . ' ' . $where . 'ORDER BY id desc ' . $where_limit);
        }
        return $query->result_array();
    }

    function edit_report($data, $id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('reporting', $data))
            return true;
        else
            return false;
    }

    function add_file($data)
    {
        if ($this->db->insert('report_media', $data))
            return $this->db->insert_id();
        else
            return false;
    }

    function report_task_count_update($id)
    {
        if ($this->db->query('UPDATE reporting SET task_count = `task_count`+1 WHERE id=' . $id . ''))
            return true;
        else
            return false;
    }

    function report_task_count_decreas($id)
    {
        if ($this->db->query('UPDATE reporting SET task_count = `task_count`-1 WHERE id=' . $id . ''))
            return true;
        else
            return false;
    }

    function delete_report($id)
    {
        /* $query = $this->db->query("SELECT * FROM report_media WHERE type_id=$id AND type='report' ");
        $data = $query->result_array();
        $abspath = getcwd();
         foreach ($data as $row) {
            unlink('assets/report/' . $row['file_name']);
        } 
        $this->db->delete('report_media', array('type_id' => $id, 'type' => 'report')); */

        if ($this->db->delete('reporting', array('id' => $id))) {
            return true;
        } else {
            return false;
        }
    }

    function delete_file($id)
    {
        $query = $this->db->query('SELECT * FROM report_media WHERE id=' . $id . '');
        $data = $query->result_array();
        if (!empty($data)) {
            $abspath = getcwd();
            if (unlink('assets/report/' . $data[0]['file_name'])) {
                $this->db->delete('report_media', array('id' => $id));
                return true;
            } else {
                return false;
            }
        }
    }

    function get_report_by_id($report_id)
    {
        $query = $this->db->query('SELECT * FROM reporting WHERE id=' . $report_id . ' ');
        return $query->result_array();
    }

    function get_files($type_id, $type)
    {
        $query = $this->db->query('SELECT * FROM report_media WHERE type="' . $type . '" AND type_id=' . $type_id . '');
        return $query->result_array();
    }
    function get_report_users($id)
    {
        $query = $this->db->query('SELECT user_id FROM reporting WHERE id=' . $id);
        return $query->result_array();
    }
    function get_report_clients($id)
    {
        $query = $this->db->query('SELECT client_id FROM reporting WHERE id=' . $id);
        // print_r($this->db->last_query());
        return $query->result_array();
    }
    function send_email($user_ids, $report_id, $admin_id)
    {

        $recepients = array();
        $report = $this->get_report_by_id($report_id);
        $admin = $this->users_model->get_user_by_id($admin_id);
        $admin_name = $admin[0]['first_name'] . ' ' . $admin[0]['last_name'];
        for ($i = 0; $i < count($user_ids); $i++) {
            $query = $this->db->query("SELECT email FROM users WHERE id=" . $user_ids[$i]);
            $data = $query->result_array();
            if (isset($data[0]) && !empty($data[0])) {
                array_push($recepients, $data[0]['email']);
            }
        }
        $recepients = implode(",", $recepients);
        $from_email = get_admin_email();
        $mail_type = get_mail_type();
        $this->email->set_newline("\r\n");
        $this->email->set_mailtype($mail_type);
        $this->email->from($from_email, get_compnay_title());
        $this->email->to($recepients);
        $this->email->subject('Added in new report');
        $body = "<html>
                <body>
                    <h1>New report</h1>
                    <p>" . $admin_name . " just added you in new report <b>" . $report[0]['title'] . "</b> ID#" . $report[0]['id'] . " </p>
                    <p>Go To your report <a href='" . base_url() . "'>Click Here</a></p>
                </body>
            </html>";

        $this->email->message($body);
        $this->email->send();
    }
    function get_reports($workspace_id)
    {
        $this->db->where('workspace_id', $workspace_id);
        $this->db->from('reporting');
        $this->db->order_by("id", "desc");
        $query = $this->db->get();
        return $query->result_array();
    }
}
