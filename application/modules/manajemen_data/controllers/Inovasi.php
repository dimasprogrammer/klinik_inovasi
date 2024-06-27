<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/**
 * Description of inovasi class
 *
 * @author Dimas Dwi Randa
 */

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Inovasi extends SLP_Controller
{
    protected $_vwName  = '';
    protected $_uriName = '';
    protected $_vwNameIndikator  = '';
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('model_inovasi' => 'minov', 'model_nilai_indikator' => 'mindi', 'master/model_master' => 'mmas', 'manajemen/model_users' => 'muser'));
        $this->_vwName = 'inovasi';
        $this->_vwNameIndikator = 'indikator';
        $this->_uriName = 'manajemen_data/inovasi';
    }

    private function validasiDataValue()
    {
        $this->form_validation->set_rules('nama_inovasi', 'Nama inovasi', 'required|trim');
        $this->form_validation->set_rules('rancang_bangun', 'Rancang Bangun', array('required', array(
            'limit_word_check',
            function ($txt) {
                $trim = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($txt))))));
                $words = explode(' ', $trim);
                $total = count($words);

                if ($total < 300) {
                    return false;
                } else {
                    return true;
                }
            }
        )));

        $this->form_validation->set_message('limit_word_check', '%s minimal 300 kata.');
        validation_message_setting();
        if ($this->form_validation->run() == FALSE)
            return false;
        else
            return true;
    }

    private function validasiDataValuePermohonan()
    {
        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap', 'required|trim');
        validation_message_setting();
        if ($this->form_validation->run() == FALSE)
            return false;
        else
            return true;
    }

    private function validasiDataValuePermohonanUpdate()
    {
        $this->form_validation->set_rules('nama_lengkap_update', 'Nama Lengkap', 'required|trim');
        $this->form_validation->set_rules('jabatan_update', 'Jabatan', 'required|trim');
        $this->form_validation->set_rules('instansi_update', 'Nama Instansi', 'required|trim');
        $this->form_validation->set_rules('no_hp_update', 'No HP', 'required|trim');
        $this->form_validation->set_rules('email_update', 'Alamat Email', 'required|trim');
        $this->form_validation->set_rules('status_permohonan_update', 'Status', 'required|trim');
        validation_message_setting();
        if ($this->form_validation->run() == FALSE)
            return false;
        else
            return true;
    }

    public function index()
    {
        $this->breadcrumb->add('Dashboard', site_url('home'));
        $this->breadcrumb->add('Manajemen', '#');
        $this->breadcrumb->add('inovasi', site_url($this->_uriName));
        $this->session_info['page_name']       = 'Manajemen Data Inovasi Instansi';
        $this->session_info['siteUri']         = $this->_uriName;
        $this->session_info['page_js']         = $this->load->view($this->_vwName . '/vjs', array('siteUri' => $this->_uriName), true);
        $this->session_info['bentuk_inovasi']  = $this->mmas->getDataBentukInovasi();
        $this->session_info['tematik']         = $this->mmas->getDataTematik();
        $this->session_info['urusan_utama']    = $this->mmas->getDataUrusanUtama();
        $this->session_info['urusan_lainnya']  = $this->mmas->getDataUrusanLainnya();
        $this->session_info['fileupload']  = $this->mmas->getJenisFile();
        $this->session_info['data_opd']    = "";
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
                $param    = $this->input->post('param', TRUE);
                $id_tahapan_inovasi = $this->input->post('id_tahapan_inovasi');
                $dataList = $this->minov->get_datatables($id_tahapan_inovasi, $param);
                $no = $this->input->post('start');
                foreach ($dataList as $key => $dl) {
                    $no++;
                    $row = array();
                    if ($dl['id_tahapan_inovasi'] == 1) {
                        $tahapan = 'Inisiatif';
                    } else if ($dl['id_tahapan_inovasi'] == 2) {
                        $tahapan = 'Uji Coba';
                    } else {
                        $tahapan = 'Penerapan';
                    }


                    $checkButtonPermohonan = $this->mindi->getSkorKematanganCheck($dl['token']);
                    if ($checkButtonPermohonan['status'] == TRUE) {
                        $buttonPermohonan = '<button type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnPermohonan" data-id="' . $dl['token'] . '" title="Permohonan Nomor Registrasi"><i class="far fa-paper-plane"></i>
                        </button>';
                    } else {
                        $buttonPermohonan = '';
                    }

                    $status = $dl['status_inovasi'];
                    if ($status == 0) {
                        $status_inovasi = '<button type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light printPDF" data-id="' . $dl['token'] . '" title="Unduh Ringkasan Profil PDF"><i class="far fa-file-pdf"></i></button>
                            
                        <a href="' . site_url('manajemen-data/inovasi/nilai_indikator/' . $dl['token']) . '" type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnIndikator" title="Indikator Satuan">
                        <i class="far fa-folder-open"></i>
                        </a>
                        ' . $buttonPermohonan . '
                        <button type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnEdit" data-id="' . $dl['token'] . '" title="Edit data"><i class="fas fa-pencil-alt"></i></button>
                        <button type="button" class="btn btn-danger btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnDelete" data-id="' . $dl['token'] . '" title="Hapus data"><i class="fas fa-trash-alt"></i></button>';
                    } else if ($status == 1 or  $status == 3) {
                        $status_inovasi = '<button type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light printPDF" data-id="' . $dl['token'] . '" title="Unduh Ringkasan Profil PDF"><i class="far fa-file-pdf"></i></button>
                        <button type="button" class="btn btn-primary btn-m px-2 py-1 my-0 mx-0 waves-effect waves-light btnPengirim" data-id="' . $dl['token'] . '" title="Lihat Data Pengirim"><i class="fas fa-user-astronaut"></i></button>';
                    } else {
                        $status_inovasi = '<button type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light printPDF" data-id="' . $dl['token'] . '" title="Unduh Ringkasan Profil PDF"><i class="far fa-file-pdf"></i></button> 
                        <a href="' . site_url('manajemen-data/inovasi/nilai_indikator/' . $dl['token']) . '" type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnIndikator" title="Indikator Satuan">
                        <i class="far fa-folder-open"></i>
                        </a>
                        <button type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnPermohonanUpdate" data-id="' . $dl['token'] . '" title="Permohonan Update Nomor Registrasi"><i class="far fa-paper-plane"></i>
                        </button>
                        <button type="button" class="btn btn-gray btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnEdit" data-id="' . $dl['token'] . '" title="Edit data"><i class="fas fa-pencil-alt"></i></button>
                        <button type="button" class="btn btn-danger btn-sm px-2 py-1 my-0 mx-0 waves-effect waves-light btnDelete" data-id="' . $dl['token'] . '" title="Hapus data"><i class="fas fa-trash-alt"></i></button>';
                    }
                    $totalKematangan = $this->mindi->getSkorNilaiKematanganWajib($dl['token']);

                    $row[] = $no;
                    $row[] = $dl['opd_id_name'];
                    $row[] = $dl['nama_inovasi'];
                    $row[] = $dl['nm_urusan_utama'];
                    $row[] = tgl_indo($dl['waktu_uji_coba']);
                    $row[] = $tahapan;
                    $row[] = tgl_indo($dl['waktu_penerapan']);
                    $row[] = $totalKematangan;
                    $row[] = convert_status_inovasi($dl['status_inovasi']);
                    $row[] = $dl['rancang_perbaikan'];
                    $row[] = $status_inovasi;
                    $data[] = $row;
                }
                $output = array(
                    "draw" => $this->input->post('draw'),
                    "recordsTotal" => $this->minov->count_all($id_tahapan_inovasi),
                    "recordsFiltered" => $this->minov->count_filtered($id_tahapan_inovasi, $param),
                    "data" => $data,
                );
            }
            //output to json format
            $this->output->set_content_type('application/json')->set_output(json_encode($output));
        }
    }

    public function create()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            if (!empty($session)) {
                if ($this->validasiDataValue() == FALSE) {
                    $result = array('status' => 'RC404', 'message' => $this->form_validation->error_array(), 'csrfHash' => $csrfHash);
                } else {
                    $data = $this->minov->insertDataInovasi();
                    if ($data['response'] == 'ERROR') {
                        $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data inovasi baru dengan nama ' . $data['nama'] . ' gagal, karena ditemukan nama yang sama'), 'csrfHash' => $csrfHash);
                    } else if ($data['response'] == 'SUCCESS') {
                        $result = array('status' => 'RC200', 'message' => 'Proses insert data inovasi baru dengan nama ' . $data['nama'] . ' sukses', 'csrfHash' => $csrfHash);
                    }
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data inovasi baru gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function details()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $contId   = $this->input->post('token', TRUE);
            if (!empty($contId) and !empty($session)) {
                $data = $this->minov->getDataDetailInovasi($contId);
                $detail = $this->minov->getDataFile($contId);
                $row = array();
                $row['nama_file'] = array();
                foreach ($detail as $key => $value) {
                    $year   = substr($value['create_date'], 0, 4);
                    $month  = substr($value['create_date'], 5, 2);
                    if ($value['nama_file'] == '') {
                        $gambar = '';
                    } else {
                        $gambar = '<a href="' . site_url('dokumen/inovasi/' . $year . '/' . $month . '/' . $value['nama_file']) . '" >' . $value['nama_file'] . '</a>';
                    }
                    $row['nama_file'][$value['id_category_file']] = '<a href="' . site_url('dokumen/inovasi/' . $year . '/' . $month . '/' . $value['nama_file']) . '" >' . $value['nama_file'] . '</a>';
                    $row['nama_file'][$value['id_category_file']] = $gambar;
                }
                $row['token']                = !empty($data) ? $data['token'] : '';
                $row['nama_inovasi']         = !empty($data) ? $data['nama_inovasi'] : '';
                $row['type_tahapan']         = !empty($data) ? $data['id_tahapan_inovasi'] : '';
                $row['type_inisiator']       = !empty($data) ? $data['id_inisiator_inovasi'] : '';
                $row['type_jenis']           = !empty($data) ? $data['id_jenis_inovasi'] : '';
                $row['id_bentuk_inovasi']    = !empty($data) ? $data['id_bentuk_inovasi'] : '';
                $row['id_tematik']           = !empty($data) ? $data['id_tematik'] : '';
                $row['id_urusan_utama']      = !empty($data) ? $data['id_urusan_utama'] : '';
                $row['id_urusan_lainnya']    = !empty($data) ? $data['id_urusan_lainnya'] : '';
                $row['waktu_uji_coba']       = !empty($data) ? $data['waktu_uji_coba'] : '';
                $row['waktu_penerapan']      = !empty($data) ? $data['waktu_penerapan'] : '';
                $row['rancang_bangun']       = !empty($data) ? $data['rancang_bangun'] : '';
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
            $contId   = escape($this->input->post('tokenId', TRUE));
            if (!empty($session) and !empty($contId)) {
                if ($this->validasiDataValue() == FALSE) {
                    $result = array('status' => 'RC404', 'message' => $this->form_validation->error_array(), 'csrfHash' => $csrfHash);
                } else {
                    $data = $this->minov->updateDataInovasi();
                    if ($data['response'] == 'ERROR') {
                        $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses update data inovasi gagal, karena data tidak ditemukan'), 'csrfHash' => $csrfHash);
                    } else if ($data['response'] == 'ERRDATA') {
                        $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses update data inovasi dengan nama ' . $data['nama'] . ' gagal, karena ditemukan nama yang sama'), 'csrfHash' => $csrfHash);
                    } else if ($data['response'] == 'SUCCESS') {
                        $result = array('status' => 'RC200', 'message' => 'Proses update data inovasi dengan nama ' . $data['nama'] . ' sukses', 'csrfHash' => $csrfHash);
                    }
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses update data inovasi gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
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
            $contId   = escape($this->input->post('tokenId', TRUE));

            if (!empty($session) and !empty($contId)) {
                $data = $this->minov->deleteDataInovasi();

                if ($data['response'] == 'ERROR') {
                    $result = array('status' => 'RC404', 'message' => 'Proses delete data inovasi gagal, karena data tidak ditemukan', 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'ERRDATA') {
                    $result = array('status' => 'RC404', 'message' => 'Proses delete data inovasi dengan nama ' . $data['nama'] . ' gagal, karena data sedang digunakan', 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses delete data inovasi dengan nama ' . $data['nama'] . ' sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 0, 'message' => 'Proses delete data inovasi gagal, mohon coba kembali', 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function nilai_indikator($token)
    {
        $dataTokenInovasi = $this->mindi->addDataInovasiIndikator($token);
        // print_r($dataTokenInovasi);
        // die;
        if (count($dataTokenInovasi) <= 0)
            redirect('manajemen_data/inovasi');

        $this->breadcrumb->add('Dashboard', site_url('home'));
        $this->breadcrumb->add('Konfirmasi Indikator', '#');
        $this->breadcrumb->add('Indikator', site_url($this->_uriName));
        $this->breadcrumb->add('create_nilai', '#');

        $this->session_info['page_name']        = "Detail Indikator";
        $this->session_info['page_js']          = $this->load->view($this->_vwNameIndikator . '/vjs', array('siteUri' => $this->_uriName), true);
        $this->session_info['token_inovasi']    = $dataTokenInovasi;
        $this->session_info['list_indikator']   = $this->mindi->getDataIndikator();
        // $this->session_info['satuan_indikator'] = $this->mindi->getDataIndikatorSatuan();
        $this->template->build($this->_vwNameIndikator . '/vindikator', $this->session_info);
    }

    public function createNilai()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            if (!empty($session)) {
                $data = $this->mindi->insertDataIndikatorNilai();
                if ($data['response'] == 'ERROR') {
                    $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data paramater gagal, karena parameter sudah di entrikan'), 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses insert data paramater sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data paramater gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function detailsIndikator()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $id_indikator   = $this->input->post('token', TRUE);
            if (!empty($id_indikator) and !empty($session)) {
                $data = $this->mindi->getDataIndikatorDetail($id_indikator);
                $dataSatuan = $this->mindi->getDataIndikatorSatuan($id_indikator);
                $row = array();
                // $row['token']        = !empty($data) ? $data['token'] : '';
                $row['id_indikator'] = !empty($data) ? $data['id_indikator'] : '';
                $row['indikator_satuan'] = !empty($dataSatuan) ? $dataSatuan : '';

                $result = array('status' => 'RC200', 'message' => $row, 'csrfHash' => $csrfHash);
            } else {
                $result = array('status' => 'RC404', 'message' => array(), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function detailsIndikatorFile()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $id_indikator   = $this->input->post('token', TRUE);
            if (!empty($id_indikator) and !empty($session)) {
                $data = $this->mindi->getDataIndikatorDetail($id_indikator);
                $row = array();
                // $row['token']        = !empty($data) ? $data['token'] : '';
                $row['id_indikator'] = !empty($data) ? $data['id_indikator'] : '';

                $result = array('status' => 'RC200', 'message' => $row, 'csrfHash' => $csrfHash);
            } else {
                $result = array('status' => 'RC404', 'message' => array(), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function createLink()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            if (!empty($session)) {
                $data = $this->mindi->insertDataIndikatorLink();
                if ($data['response'] == 'ERROR') {
                    $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data Link gagal, karena parameter sudah di entrikan'), 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses insert data Link sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data Link gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function createFile()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            if (!empty($session)) {
                $data = $this->mindi->insertDataIndikatorFile();
                if ($data['response'] == 'ERROR') {
                    $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data paramater gagal, karena parameter sudah di entrikan'), 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses insert data paramater sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data paramater gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function detailUploadHasil()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $token   = $this->input->post('token', TRUE);
            if (!empty($token) and !empty($session)) {
                $data = $this->minov->getDataDetailInovasi($token);
                $row = array();
                $row['token']        = !empty($data) ? $data['token'] : '';

                $result = array('status' => 'RC200', 'message' => $row, 'csrfHash' => $csrfHash);
            } else {
                $result = array('status' => 'RC404', 'message' => array(), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function createUploadHasil()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            if (!empty($session)) {
                $data = $this->minov->insertDataIndikatorFile();
                if ($data['response'] == 'ERROR') {
                    $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data paramater gagal, karena parameter sudah di entrikan'), 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses insert data paramater sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data paramater gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function detailPermohonan()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $token   = $this->input->post('token', TRUE);
            if (!empty($token) and !empty($session)) {
                $data = $this->minov->getDataDetailInovasi($token);
                $row = array();
                $row['token']        = !empty($data) ? $data['token'] : '';

                $result = array('status' => 'RC200', 'message' => $row, 'csrfHash' => $csrfHash);
            } else {
                $result = array('status' => 'RC404', 'message' => array(), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function createPermohonan()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            if (!empty($session)) {
                if ($this->validasiDataValuePermohonan() == FALSE) {
                    $result = array('status' => 'RC404', 'message' => $this->form_validation->error_array(), 'csrfHash' => $csrfHash);
                } else {
                    $data = $this->minov->insertDataPermohonan();
                    if ($data['response'] == 'ERROR') {
                        $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data Permohonan baru dengan nama ' . $data['nama'] . ' gagal, karena ditemukan nama yang sama'), 'csrfHash' => $csrfHash);
                    } else if ($data['response'] == 'SUCCESS') {
                        $result = array('status' => 'RC200', 'message' => 'Proses insert data Permohonan baru dengan nama ' . $data['nama'] . ' sukses', 'csrfHash' => $csrfHash);
                    }
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses insert data Permohonan baru gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function detailPermohonanUpdate()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $token   = $this->input->post('token', TRUE);
            if (!empty($token) and !empty($session)) {
                $data = $this->minov->getDataDetailPermohonanInformasi($token);
                $row = array();
                $row['token']        = !empty($data) ? $data['token'] : '';
                $row['nama_lengkap'] = !empty($data) ? $data['nama_lengkap'] : '';
                $row['jabatan']      = !empty($data) ? $data['jabatan'] : '';
                $row['instansi']     = !empty($data) ? $data['instansi'] : '';
                $row['no_hp']        = !empty($data) ? $data['no_hp'] : '';
                $row['email']        = !empty($data) ? $data['email'] : '';

                $result = array('status' => 'RC200', 'message' => $row, 'csrfHash' => $csrfHash);
            } else {
                $result = array('status' => 'RC404', 'message' => array(), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function updatePermohonan()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            if (!empty($session)) {
                if ($this->validasiDataValuePermohonanUpdate() == FALSE) {
                    $result = array('status' => 'RC404', 'message' => $this->form_validation->error_array(), 'csrfHash' => $csrfHash);
                } else {
                    $data = $this->minov->updateDataPermohonan();
                    if ($data['response'] == 'ERROR') {
                        $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses update data Permohonan baru dengan nama ' . $data['nama'] . ' gagal, karena ditemukan nama yang sama'), 'csrfHash' => $csrfHash);
                    } else if ($data['response'] == 'SUCCESS') {
                        $result = array('status' => 'RC200', 'message' => 'Proses update data Permohonan baru dengan nama ' . $data['nama'] . ' sukses', 'csrfHash' => $csrfHash);
                    }
                }
            } else {
                $result = array('status' => 'RC404', 'message' => array('isi' => 'Proses update data Permohonan baru gagal, mohon coba kembali'), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function detailPengirim()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $token   = $this->input->post('token', TRUE);
            if (!empty($token) and !empty($session)) {
                $data = $this->minov->getDataDetailPengirim($token);
                $row = array();
                $row['token']        = !empty($data) ? $data['token'] : '';
                $row['nama_lengkap'] = !empty($data) ? $data['nama_lengkap'] : '';
                $row['jabatan']      = !empty($data) ? $data['jabatan'] : '';
                $row['instansi']     = !empty($data) ? $data['instansi'] : '';
                $row['no_hp']        = !empty($data) ? $data['no_hp'] : '';
                $row['email']        = !empty($data) ? $data['email'] : '';

                $result = array('status' => 'RC200', 'message' => $row, 'csrfHash' => $csrfHash);
            } else {
                $result = array('status' => 'RC404', 'message' => array(), 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function deleteParameter()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $contId   = escape($this->input->post('tokenId', TRUE));

            if (!empty($session) and !empty($contId)) {
                $data = $this->mindi->deleteDataParameter();
                if ($data['response'] == 'ERROR') {
                    $result = array('status' => 'RC404', 'message' => 'Proses delete data parameter gagal, karena data tidak ditemukan', 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'ERRDATA') {
                    $result = array('status' => 'RC404', 'message' => 'Proses delete data parameter gagal, karena data sedang digunakan', 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses delete data parameter sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 0, 'message' => 'Proses delete data parameter gagal, mohon coba kembali', 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function deleteFilePendukung()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $contId   = escape($this->input->post('tokenId', TRUE));

            if (!empty($session) and !empty($contId)) {
                $data = $this->mindi->deleteDataIndikatorFile($contId);
                if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses delete data file pendukung sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 0, 'message' => 'Proses delete data file pendukung gagal, mohon coba kembali', 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function deleteLink()
    {
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        } else {
            $session  = $this->app_loader->current_account();
            $csrfHash = $this->security->get_csrf_hash();
            $contId   = escape($this->input->post('tokenId', TRUE));

            if (!empty($session) and !empty($contId)) {
                $data = $this->mindi->deleteDataLink();
                if ($data['response'] == 'ERROR') {
                    $result = array('status' => 'RC404', 'message' => 'Proses delete data Link gagal, karena data tidak ditemukan', 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'ERRDATA') {
                    $result = array('status' => 'RC404', 'message' => 'Proses delete data Link gagal, karena data sedang digunakan', 'csrfHash' => $csrfHash);
                } else if ($data['response'] == 'SUCCESS') {
                    $result = array('status' => 'RC200', 'message' => 'Proses delete data Link sukses', 'csrfHash' => $csrfHash);
                }
            } else {
                $result = array('status' => 0, 'message' => 'Proses delete data Link gagal, mohon coba kembali', 'csrfHash' => $csrfHash);
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        }
    }

    public function export_to_pdf()
    {
        $token = $this->input->get('token', TRUE);

        $this->session_info['list_indikator']   = $this->mindi->getDataIndikator();
        $data['data'] = $this->minov->getDataInovasiCetakPDF($token);
        // $data['data'] = 'panggil';


        // panggil library yang kita buat sebelumnya yang bernama pdfgenerator
        // $view_data   = base_url() . 'assets/img/balitbang.png';
        // var_dump($view_data);
        // die;
        // $path = $view_data;
        // $type = pathinfo($path, PATHINFO_EXTENSION);
        // $database = file_get_contents($path);

        // $data['test'] = REALPATH . '/assets/img/balitbang.png';

        // $data['base64'] = 'data:image/' . $type . ';base64,' . base64_encode($database);
        $this->load->library('pdfgenerator');
        // title dari pdf
        // $data['title_pdf']  =   'DATA USULAN ' . $data['data']['nama_inovasi'];
        // filename dari pdf ketika didownload
        $file_pdf = "testubg";
        // setting paper
        $paper = 'legal';
        // $paper = 'legal';
        //orientasi paper potrait / landscape
        $orientation = "portrait";
        $html = $this->load->view($this->_vwName . '/vprint', $data, true);
        // run dompdf
        $this->pdfgenerator->generate($html, $file_pdf, $paper, $orientation);
    }



    public function export_to_excel()
    {
        require realpath('vendor/autoload.php');

        $opd    = escape($this->input->get('opd', TRUE));
        $dataInovasi = $this->minov->getDataCetakExcel($opd);

        $noRow = 0;
        $baseRow = 6;
        $spreadsheet = new Spreadsheet();
        $templatePath = 'repository/profil_excel.xlsx';
        $spreadsheet = IOFactory::load($templatePath);
        $activeWorksheet = $spreadsheet->getActiveSheet();
        if (count($dataInovasi) > 0) {
            foreach ($dataInovasi as $key => $dInov) {

                $dataBobot      = $this->minov->getTotalBobotByIdInovasi($dInov['token']);
                $checkDataLink  = $this->minov->cekDokumenLink($dInov['token']);
                if ($dInov['id_jenis_inovasi'] == 1) {
                    $jenis = 'Digital';
                } else {
                    $jenis = 'Non Digital';
                }

                if ($dInov['id_inisiator_inovasi'] == 1) {
                    $inisiator = 'Kepala OPD';
                } else if ($dInov['id_inisiator_inovasi'] == 2) {
                    $inisiator = 'Anggota DPRD';
                } else if ($dInov['id_inisiator_inovasi'] == 3) {
                    $inisiator = 'OPD';
                } else if ($dInov['id_inisiator_inovasi'] == 4) {
                    $inisiator = 'ASN';
                } else {
                    $inisiator = 'Masyarakat';
                }
                if ($dInov['id_tahapan_inovasi'] == 1) {
                    $tahapan = 'Inisiatif';
                } else if ($dInov['id_tahapan_inovasi'] == 2) {
                    $tahapan = 'Uji Coba';
                } else {
                    $tahapan = 'Masyarakat';
                }
                $create_date = $dInov['create_date'];
                $tanggal = tgl_indonesia($create_date);
                $noRow++;
                $row = $baseRow + $noRow;
                $activeWorksheet->insertNewRowBefore($row, 1);
                $activeWorksheet->setCellValue('A' . $row, $noRow);
                $activeWorksheet->setCellValue('B' . $row, isset($dInov['nama_inovasi']) ? $dInov['nama_inovasi'] : '-');
                $activeWorksheet->setCellValue('C' . $row, isset($dInov['opd_id_name']) ? $dInov['opd_id_name'] : '-');
                $activeWorksheet->setCellValue('D' . $row, isset($dInov['fullname']) ? $dInov['fullname'] : '-');
                $activeWorksheet->setCellValue('E' . $row, '-');
                $activeWorksheet->setCellValue('F' . $row, isset($dInov['nm_bentuk']) ? $dInov['nm_bentuk'] : '-');
                $activeWorksheet->setCellValue('G' . $row, isset($jenis) ? $jenis : '-');
                $activeWorksheet->setCellValue('H' . $row, isset($inisiator) ? $inisiator : '-');
                $activeWorksheet->setCellValue('I' . $row, isset($dInov['nm_urusan_utama']) ? $dInov['nm_urusan_utama'] : '-');
                $activeWorksheet->setCellValue('J' . $row, isset($dataBobot) ? $dataBobot : 0);
                $activeWorksheet->setCellValue('K' . $row, isset($tahapan) ? $tahapan : '-');
                $activeWorksheet->setCellValue('L' . $row, isset($tanggal) ? $tanggal : '-');
                $activeWorksheet->setCellValue('M' . $row, 'Tidak');
                $activeWorksheet->setCellValue('N' . $row, '-');
                $activeWorksheet->setCellValue('O' . $row, '-');
                $activeWorksheet->setCellValue('P' . $row, isset($checkDataLink['thumbnail']) ? $checkDataLink['thumbnail'] : '-');
            }
        } else {
            $row = $baseRow + 1;
            $activeWorksheet->insertNewRowBefore($row, 1);
            $activeWorksheet->setCellValue('A' . $row, 1);
            $activeWorksheet->setCellValue('B' . $row, '');
            $activeWorksheet->setCellValue('C' . $row, '');
            $activeWorksheet->setCellValue('D' . $row, '');
            $activeWorksheet->setCellValue('E' . $row, '');
            $activeWorksheet->setCellValue('F' . $row, '');
            $activeWorksheet->setCellValue('G' . $row, '');
            $activeWorksheet->setCellValue('H' . $row, '');
            $activeWorksheet->setCellValue('I' . $row, '');
            $activeWorksheet->setCellValue('J' . $row, '');
            $activeWorksheet->setCellValue('K' . $row, '');
            $activeWorksheet->setCellValue('L' . $row, '');
            $activeWorksheet->setCellValue('M' . $row, '');
            $activeWorksheet->setCellValue('N' . $row, '');
            $activeWorksheet->setCellValue('O' . $row, '');
            $activeWorksheet->setCellValue('P' . $row, '');
        }
        $activeWorksheet->removeRow($baseRow, 1);

        $fileName = 'contoh_spreadsheet.xlsx';

        // Atur header HTTP agar file dapat diunduh
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Tanggal di masa lalu
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // Selalu modifikasi
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        // Tulis file ke output
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}

// This is the end of fungsi class
