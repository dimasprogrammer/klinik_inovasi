<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/**
 * Description of indikator_parameter class
 *
 * @author Dimas Dwi Randa
 */

class Indikator_parameter extends SLP_Controller
{
    protected $_vwName  = '';
    protected $_uriName = '';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('model_indikator_parameter' => 'mtem', 'master/model_master' => 'mmas'));
        $this->_vwName = 'indikator_parameter';
        $this->_uriName = 'manajemen_data/indikator_parameter';
    }

    private function validasiDataValue()
    {
        $this->form_validation->set_rules('nilai_parameter', 'Nama Indikator Parameter', 'required|trim');
        validation_message_setting();
        if ($this->form_validation->run() == FALSE)
            return false;
        else
            return true;
    }

    public function index()
    {
        $this->breadcrumb->add('Home', site_url('home'));
        $this->breadcrumb->add('Manajemen', '#');
        $this->breadcrumb->add('Indikator Parameter', site_url($this->_uriName));
        $this->session_info['page_name']        = 'Manajemen Indikator Parameter';
        $this->session_info['siteUri']          = $this->_uriName;
        $this->session_info['page_js']          = $this->load->view($this->_vwName . '/vjs', array('siteUri' => $this->_uriName), true);
        $this->session_info['indikator_satuan'] = $this->mmas->getDataIndikatorSatuan();
        $this->template->build($this->_vwName . '/vpage', $this->session_info);
    }

    public function listview()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $data = array();
            $session = $this->app_loader->current_account();
            if (isset($session)) {
                $dataList = $this->mtem->get_datatables();
                $no = $this->input->post('start');
                foreach ($dataList as $key => $dl) {
                    $no++;
                    $row = array();
                    $row[] = $no;
                    $row[] = $dl['opd_id_name'];
                    $row[] = $dl['nilai_parameter'];
                    $row[] = '<button type="button" class="btn btn-orange btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnEdit" data-id="' . $this->encryption->encrypt($dl['id_indikator_nilai']) . '" title="Edit data"><i class="fas fa-pencil-alt"></i></button>
                              <button type="button" class="btn btn-danger btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnDelete" data-id="' . $this->encryption->encrypt($dl['id_indikator_nilai']) . '" title="Hapus data"><i class="fas fa-trash-alt"></i></button>';
                    $data[] = $row;
                }
                $output = array(
                    "draw" => $this->input->post('draw'),
                    "recordsTotal" => $this->mtem->count_all(),
                    "recordsFiltered" => $this->mtem->count_filtered(),
                    "data" => $data,
                );
            }
            //output to json format
            $this->output->set_content_type('application/json')->set_output(json_encode($output));
        }
    }

    public function details()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $funcId   = $this->input->post('token', TRUE);
            if (!empty($funcId) and !empty($session)) {
                $data = $this->mtem->getDataDetailIndikatorParameter($this->encryption->decrypt($funcId));
                $row = array();
                $row['id_indikator_satuan']   = !empty($data) ? $data['id_indikator_satuan'] : '';
                $row['nm_indikator']   = !empty($data) ? $data['nm_indikator'] : '';
                $result = array('status' => 'RC200', 'message' => $row, 'csrfHash' => $csrfHash);
            } else {
                $result = array('status' => 'RC404', 'message' => array(), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function update()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $funcId   = escape($this->input->post('tokenId', TRUE));
            if (!empty($session) and !empty($funcId)) {
                $data = $this->mtem->updateDataIndikatorParameter();
                if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses update data Indikator Parameter sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses update data Indikator Parameter gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function delete()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $funcId   = escape($this->input->post('tokenId', TRUE));
            if (!empty($session) and !empty($funcId)) {
                $data = $this->mtem->deleteDataindikator_parameter();
                if ($data['response'] == 'ERROR') {
                    $result = array('status' => 'RC404', 'message' => 'Proses delete data indikator_parameter gagal, karena data tidak ditemukan', 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'ERRDATA') {
                    $result = array('status' => 'RC404', 'message' => 'Proses delete data indikator_parameter dengan nama ' . $data['nama'] . ' gagal, karena data sedang digunakan', 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses delete data indikator_parameter dengan nama ' . $data['nama'] . ' sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 0, 'message' => 'Proses delete data indikator_parameter gagal, mohon coba kembali', 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }
}

// This is the end of indikator_parameter class
