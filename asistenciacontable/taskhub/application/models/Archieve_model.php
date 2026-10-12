<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Archieve_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'file']);
    }

    function get_archieves_list($workspace_id, $user_id)
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
            $where = " and (id like '%" . $search . "%' OR title like '%" . "%' OR archieve_type like '%" . $search . "%' OR description like '%" . $search . "%' OR archieves_userss like '%" . $search . "%' OR archieves_users like '%" . "%' OR date like '%" . $search . "%')";
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
            $query = $this->db->query("SELECT COUNT(id) as total FROM archieve WHERE FIND_IN_SET($user_id,user_id) AND workspace_id=$workspace_id " . $where);
        } else {
            $query = $this->db->query("SELECT COUNT(id) as total FROM archieve WHERE FIND_IN_SET($user_id,client_id) AND workspace_id=$workspace_id " . $where);
        }

        $res = $query->result_array();
        foreach ($res as $row) {
            $total = $row['total'];
        }

        if (!is_client($user_id)) {
            $query = $this->db->query("SELECT * FROM archieve WHERE FIND_IN_SET($user_id,user_id) AND workspace_id= $workspace_id " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit);
        } else {
            $query = $this->db->query("SELECT * FROM archieve WHERE FIND_IN_SET($user_id,client_id) AND workspace_id= $workspace_id " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit);
        }
        // print_r($this->db->last_query());    
        $res = $query->result_array();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();

        $this->config->load('taskhub');
        $progress_bar_classes = $this->config->item('progress_bar_classes');

        foreach ($res as $row) {
            $profile = '';
            $cprofile = '';
            $tempRow['id'] = $row['id'];

            $archieves_user_ids = explode(',', $row['user_id']);
            $archieves_userss = $this->users_model->get_user_array_responce($archieves_user_ids);
            $i = 0;
            $j = count($archieves_userss);
            foreach ($archieves_userss as $archieves_users) {
                if ($i < 2) {
                    if (isset($archieves_users['profile']) && !empty($archieves_users['profile'])) {
                        $profile .= '<a href="' . base_url('assets/profiles/' . $archieves_users['profile']) . '" data-lightbox="images" data-title="' . $archieves_users['first_name'] . '">
                        <img alt="image" class="mr-1 rounded-circle" width="30" src="' . base_url('assets/profiles/' . $archieves_users['profile']) . '">
                        </a>';
                    } else {
                        $profile .= '<figure class="avatar mr-1 avatar-sm" data-toggle="tooltip" data-title="' . $archieves_users['first_name'] . '" data-initial="' . mb_substr($archieves_users['first_name'], 0, 1) . '' . mb_substr($archieves_users['last_name'], 0, 1) . '">
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

            $archieves_client_ids = explode(',', $row['client_id']);
            $archieves_clients = $this->users_model->get_user_array_responce($archieves_client_ids);

            $ci = 0;
            $cj = count($archieves_clients);
            foreach ($archieves_clients as $archieves_client) {
                if ($ci < 2) {
                    if (isset($archieves_client['profile']) && !empty($archieves_client['profile'])) {

                        $cprofile .= '<a href="' . base_url('assets/profiles/' . $archieves_client['profile']) . '" data-lightbox="images" data-title="' . $archieves_client['first_name'] . '">
                        <img alt="image" class="mr-3 rounded-circle" width="50" src="' . base_url('assets/profiles/' . $archieves_client['profile']) . '">
                        </a>';
                    } else {
                        $cprofile .= '<figure class="avatar mr-1 avatar-sm" data-toggle="tooltip" data-title="' . $archieves_client['first_name'] . '" data-initial="' . mb_substr($archieves_client['first_name'], 0, 1) . '' . mb_substr($archieves_client['last_name'], 0, 1) . '">
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

            $action = '<a href="' . base_url('archieve/details/' . $row['id']) . '" class="btn btn-light mr-2"><i class="fas fa-eye"></i></a>';

            if ($this->ion_auth->is_admin($user_id) || is_editor($user_id)) {
                $action .= '<div class="dropdown card-widgets">
                                <a href="#" class="btn btn-light" data-toggle="dropdown" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item has-icon modal-edit-archieve-ajax" href="#" data-id="' . $row['id'] . '"><i class="fas fa-pencil-alt"></i>' . (!empty($this->lang->line('label_edit')) ? $this->lang->line('label_edit') : 'Edit') . '</a>
                                    <a class="dropdown-item has-icon delete-archieve-alert" href="' . base_url('archieve/delete/' . $row['id']) . '" data-archieve_id="' . $row['id'] . '"><i class="far fa-trash-alt"></i>' . (!empty($this->lang->line('label_delete')) ? $this->lang->line('label_delete') : 'Delete') . '</a>
                                </div>
                            </div>';
            }

            $action_btns = '<div class="btn-group no-shadow">
                        ' . $action . '
                        </div>';
            $tempRow['archieves_userss'] = $profiles;
            $tempRow['archieves_clientss'] = $cprofiles;
            $tempRow['title'] = $row['title'];
            $tempRow['date'] = $row['date'];
            $tempRow['archieve_type'] = $row['archieve_type'];
            $tempRow['description'] = mb_substr($row['description'], 0, 20) . '...';
            $tempRow['action'] = $action_btns;

            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        print_r(json_encode($bulkData));
    }

    function create_archieve($data)
    {
        if ($this->db->insert('archieve', $data))
            return $this->db->insert_id();
        else
            return false;
    }


    function get_archieve($workspace_id, $user_id, $filter = '', $user_type = 'normal', $limit = '', $start = '')
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
            $query = $this->db->query('SELECT * FROM archieve WHERE FIND_IN_SET(' . $user_id . ',`client_id`) AND workspace_id=' . $workspace_id . ' ' . $where . 'ORDER BY id desc ' . $where_limit);
        } else {
            $query = $this->db->query('SELECT * FROM archieve WHERE FIND_IN_SET(' . $user_id . ',`user_id`) AND workspace_id=' . $workspace_id . ' ' . $where . 'ORDER BY id desc ' . $where_limit);
        }
        return $query->result_array();
    }

    function edit_archieve($data, $id)
    {
        $this->db->where('id', $id);
        if ($this->db->update('archieve', $data))
            return true;
        else
            return false;
    }

    function add_file($data)
    {
        if ($this->db->insert('archieve_media', $data))
            return $this->db->insert_id();
        else
            return false;
    }

    function archieve_task_count_update($id)
    {
        if ($this->db->query('UPDATE archieve SET task_count = `task_count`+1 WHERE id=' . $id . ''))
            return true;
        else
            return false;
    }

    function archieve_task_count_decreas($id)
    {
        if ($this->db->query('UPDATE archieve SET task_count = `task_count`-1 WHERE id=' . $id . ''))
            return true;
        else
            return false;
    }

    function delete_archieve($id)
    {
        /* $query = $this->db->query("SELECT * FROM archieve_media WHERE type_id=$id AND type='archieve' ");
        $data = $query->result_array();
        $abspath = getcwd();
         foreach ($data as $row) {
            unlink('assets/archieve/' . $row['file_name']);
        } 
        $this->db->delete('archieve_media', array('type_id' => $id, 'type' => 'archieve')); */

        if ($this->db->delete('archieve', array('id' => $id))) {
            return true;
        } else {
            return false;
        }
    }

    function delete_file($id)
    {
        $query = $this->db->query('SELECT * FROM archieve_media WHERE id=' . $id . '');
        $data = $query->result_array();
        if (!empty($data)) {
            $abspath = getcwd();
            if (unlink('assets/archieve/' . $data[0]['file_name'])) {
                $this->db->delete('archieve_media', array('id' => $id));
                return true;
            } else {
                return false;
            }
        }
    }

    function get_archieve_by_id($archieve_id)
    {
        $query = $this->db->query('SELECT * FROM archieve WHERE id=' . $archieve_id . ' ');
        return $query->result_array();
    }

    function get_files($type_id, $type)
    {
        $query = $this->db->query('SELECT * FROM archieve_media WHERE type="' . $type . '" AND type_id=' . $type_id . '');
        return $query->result_array();
    }
    function get_archieve_users($id)
    {
        $query = $this->db->query('SELECT user_id FROM archieve WHERE id=' . $id);
        return $query->result_array();
    }
    function get_archieve_clients($id)
    {
        $query = $this->db->query('SELECT client_id FROM archieve WHERE id=' . $id);
        // print_r($this->db->last_query());
        return $query->result_array();
    }
    function send_email($user_ids, $archieve_id, $admin_id)
    {

        $recepients = array();
        $archieve = $this->get_archieve_by_id($archieve_id);
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
        $this->email->subject('Added in new archieve');
        $body = "<html>
                <body>
                    <h1>New archieve</h1>
                    <p>" . $admin_name . " just added you in new archieve <b>" . $archieve[0]['title'] . "</b> ID#" . $archieve[0]['id'] . " </p>
                    <p>Go To your archieve <a href='" . base_url() . "'>Click Here</a></p>
                </body>
            </html>";

        $this->email->message($body);
        $this->email->send();
    }
    function get_archieves($workspace_id)
    {
        $this->db->where('workspace_id', $workspace_id);
        $this->db->from('archieve');
        $this->db->order_by("id", "desc");
        $query = $this->db->get();
        return $query->result_array();
    }
}
