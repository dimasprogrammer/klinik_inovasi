<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/**
 * Description of nontpp model
 *
 * @author prima aulia
 */

class Mhome extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getInovasiYangDilaporkan()
    {
        $iduser = $this->app_loader->current_userid();
        $this->db->select('count(a.id_inovasi) as total_inovasi
						');
        // $query = $this->db->get('ms_inovasi a');
        $this->db->from('ms_inovasi a');
        $this->db->join('xi_sa_users b', 'a.create_by = b.token', 'inner');
        if ($this->app_loader->is_operator()) {
            $this->db->where('a.create_by', $iduser);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    public function getInovasiYangDikirim()
    {
        $iduser = $this->app_loader->current_userid();
        $this->db->select('count(a.id_inovasi) as total_inovasi
						');
        // $query = $this->db->get('ms_inovasi a');
        $this->db->from('ms_inovasi a');
        $this->db->join('xi_sa_users b', 'a.create_by = b.token', 'inner');
        $this->db->where('a.status_permohonan', 1);
        if ($this->app_loader->is_operator()) {
            $this->db->where('a.create_by', $iduser);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    public function getInovasiTotalSkor()
    {
        $iduser = $this->app_loader->current_userid();
        $this->db->select('count(a.nilai_parameter) as total_parameter
						');
        // $query = $this->db->get('ms_inovasi a');
        $this->db->from('ms_indikator_nilai a');
        $this->db->join('xi_sa_users d', 'a.create_by = d.token', 'inner');
        if ($this->app_loader->is_operator()) {
            $this->db->where('a.create_by', $iduser);
        }
        $query = $this->db->get();
        return $query->row_array();
    }
}

// This is the end of auth signin model
