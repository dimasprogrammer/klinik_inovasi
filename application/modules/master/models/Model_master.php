<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/**
 * Description of model odp
 *
 * @author Dimas Dwi Randa
 */

class Model_master extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDataIndikator()
    {
        $this->db->order_by('id_indikator ASC');
        $query = $this->db->get('cx_indikator');
        $ttd[''] = 'Pilih Indikator';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ttd[$row['id_indikator']] = $row['nm_indikator'];
            }
        }
        return $ttd;
    }



    public function getDataBentukInovasi()
    {
        $this->db->order_by('id_bentuk_inovasi ASC');
        $query = $this->db->get('cx_bentuk_inovasi');
        $ttd[''] = 'Pilih Bentuk Inovasi';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ttd[$row['id_bentuk_inovasi']] = $row['nm_bentuk'];
            }
        }
        return $ttd;
    }

    public function getDataTematik()
    {
        $this->db->order_by('id_tematik ASC');
        $query = $this->db->get('cx_tematik');
        $ttd[''] = 'Pilih Tematik';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ttd[$row['id_tematik']] = $row['nm_tematik'];
            }
        }
        return $ttd;
    }

    public function getDataUrusanUtama()
    {
        $this->db->order_by('id_urusan_utama ASC');
        $query = $this->db->get('cx_urusan_utama');
        $ttd[''] = 'Pilih Urusan Utama';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ttd[$row['id_urusan_utama']] = $row['nm_urusan_utama'];
            }
        }
        return $ttd;
    }

    public function getJenisFile()
    {
        $this->db->select('id_category_file,
                           nm_category');
        $this->db->from('cx_category_file');
        $this->db->order_by('id_category_file ', 'ASC');
        // $this->db->where('id_status', 1);
        $query = $this->db->get();
        return $query->result();
    }

    public function getDataUrusanLainnya()
    {
        $this->db->order_by('id_urusan_lainnya ASC');
        $query = $this->db->get('cx_urusan_lainnya');
        $ttd[''] = 'Pilih Urusan Lainnya';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ttd[$row['id_urusan_lainnya']] = $row['nm_urusan_lainnya'];
            }
        }
        return $ttd;
    }

    public function getDataIndikatorSatuan()
    {
        $this->db->order_by('id_indikator_satuan ASC');
        $query = $this->db->get('cx_indikator_satuan');
        $ttd[''] = 'Pilih Urusan Lainnya';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $ttd[$row['id_indikator_satuan']] = $row['nm_indikator_satuan'];
            }
        }
        return $ttd;
    }

    public function getDataProvince()
    {
        $this->db->where('id', '13');
        $this->db->order_by('id ASC');
        $query = $this->db->get('wa_province');
        $dd_prov[''] = 'Pilih Provinsi';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $dd_prov[$row['id']] = $row['name'];
            }
        }
        return $dd_prov;
    }

    public function getDataRegency()
    {
        $this->db->where('province_id', '13');
        $this->db->order_by('id ASC');
        $query = $this->db->get('wa_regency');
        $dd_reg[''] = 'Pilih Daerah';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $dd_reg[$row['id']] = ($row['status'] == 1) ? "KAB " . $row['name'] : $row['name'];
            }
        }
        return $dd_reg;
    }

    public function getDataRegencyByProvince($id)
    {
        $this->db->where('province_id', $id);
        $this->db->order_by('status ASC');
        $this->db->order_by('name ASC');
        $query = $this->db->get('wa_regency');
        return $query->result_array();
    }

    public function getDataDistrictByRegency($id)
    {
        $this->db->where('regency_id', $id);
        $this->db->order_by('id ASC');
        $query = $this->db->get('wa_district');
        return $query->result_array();
    }

    public function getDataVillageByDistrict($id)
    {
        $this->db->where('district_id', $id);
        $this->db->order_by('id ASC');
        $query = $this->db->get('wa_village');
        return $query->result_array();
    }

    public function getDataStatusNikah()
    {
        $this->db->order_by('id_nikah ASC');
        $query = $this->db->get('ref_status_nikah');
        $dd_data[''] = 'Pilih Data';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $dd_data[$row['id_nikah']] = $row['status_nikah'];
            }
        }
        return $dd_data;
    }

    public function getDataStudy()
    {
        $this->db->order_by('id ASC');
        $query = $this->db->get('ref_pendidikan');
        $dd_data[''] = 'Pilih Data';
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $dd_data[$row['id']] = $row['study'];
            }
        }
        return $dd_data;
    }
}

// This is the end of auth signin model
