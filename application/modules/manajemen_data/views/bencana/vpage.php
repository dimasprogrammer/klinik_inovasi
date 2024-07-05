<section class="mb-5 pb-4 mt-4">
    <?php echo $this->session->flashdata('message'); ?>
    <div id="errSuccess"></div>
    <div class="row" id="formParent">
        <div class="col-xl-12 col-md-12 mb-xl-0 mb-4">
            <div class="card card-cascade narrower z-depth-1">
                <div class="view view-cascade gradient-card-header magenta-gradient narrower py-1 mx-4 d-flex justify-content-between align-items-center">
                    <h6 class="white-text font-weight-normal mt-2">
                        <i class="fas fa-table"></i>
                        List Data User
                    </h6>
                    <div class="clearfix">
                        <a type="button" href="<?php echo site_url(isset($siteUri) ? $siteUri : '#'); ?>" class="btn btn-white btn-rounded waves-effect waves-light px-2 py-2 font-weight-bold" name="button"><i class="fas fa-sync-alt"></i> Refresh Data</a>
                        <button type="button" class="btn btn-blue btn-rounded waves-effect waves-light px-3 py-2 font-weight-bold" id="btnAdd"><i class="fas fa-plus-circle"></i> Tambah Baru</button>

                        <a href="javascript:void(0);" class="btnFilter btn btn-white btn-rounded waves-effect waves-light px-3 py-2 font-weight-bold">
                            <i class="fas fa-sliders-h"></i> Filter Data
                        </a>
                        <button type="button" class="btn btn-white btn-rounded waves-effect waves-light px-3 py-2 font-weight-bold" name="printExcelAll" id="printExcelAll"><i class="far fa-file-excel"></i> cetak excel </button>
                    </div>
                </div>
                <div class="card-body mb-0">
                    <div class="row mb-3 mt-1">

                        <div class="col-12 col-mb-12 mb-2">
                            <?php echo form_open(site_url('#'), array('id' => 'formFilter', 'style' => 'display:none;')); ?>
                            <div class="card rgba-grey-slight">
                                <div class="card-body">
                                    <div class="form-row mb-3 ">
                                        <div class="col-12 col-md-3">
                                            <label for="fullname" class="control-label font-weight-bolder">Nama Lengkap</label>
                                            <input type="text" class="form-control" name="fullname" placeholder="Nama Lengkap" value="<?php echo $this->input->post('fullname', TRUE); ?>">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label for="username" class="control-label font-weight-bolder">Username</label>
                                            <input type="text" class="form-control" name="username" placeholder="Username" value="<?php echo $this->input->post('username', TRUE); ?>">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label for="group" class="control-label font-weight-bolder">Group User</label>
                                            <?php echo form_dropdown('group', isset($group_user) ? $group_user : array('' => 'Pilih Group User'), $this->input->post('group', TRUE), 'class="form-control select-all" style="width:100%"'); ?>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label for="blokir" class="control-label font-weight-bolder">Blokir</label>
                                            <?php echo form_dropdown('blokir', array('' => 'Pilih Data', 1 => 'Blokir', 0 => 'Tidak Blokir'), $this->input->post('blokir', TRUE), 'class="form-control select-all" style="width:100%"'); ?>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label for="status" class="control-label font-weight-bolder">Status</label>
                                            <?php echo form_dropdown('status', array('' => 'Pilih Status', 1 => 'Aktif', 0 => 'Tidak Aktif'), $this->input->post('status', TRUE), 'class="form-control select-all" style="width:100%"'); ?>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="col-12 col-md-12">
                                            <div class="d-flex justify-content-lg-start align-items-center" style="margin-right: -5px;">
                                                <button type="submit" class="btn btn-rounded btn-primary waves-effect waves-light px-3 py-2 font-weight-bold" name="filter" id="filter"><i class="fas fa-filter"></i> Lakukan Pencarian</button>
                                                <button type="button" class="btn btn-rounded btn-danger waves-effect waves-light px-3 py-2 font-weight-bold" name="cancel" id="cancel"><i class="fas fa-sync-alt"></i> Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                    <div class="table-responsive-md">
                        <table cellspacing="0" class="table table-striped table-borderless table-hover table-sm" id="tblList" width="100%">
                            <thead>
                                <tr>
                                    <th width="3%">
                                        <div class="custom-control custom-checkbox mt-0 pt-0">
                                            <input type="checkbox" class="custom-control-input" id="checkAll">
                                            <label class="custom-control-label font-weight-bolder" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th width="3%" class="font-weight-bold">#</th>
                                    <th width="30%" class="font-weight-bold">Jenis Bencana</th>
                                    <th width="30%" class="font-weight-bold">Tanggal Bencana</th>
                                    <th width="30%" class="font-weight-bold">Jam Bencana</th>
                                    <th width="20%" class="font-weight-bold">Create Date</th>
                                    <th width="10%" class="font-weight-bold">Status</th>
                                    <th width="3%" class="font-weight-bold">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!------------------------------------ FORM ENTRI DATA BENCANA -------------------------------------------->
<div class="modal fade" id="modalEntryForm" tabindex="-1" role="dialog" aria-labelledby="modalEntryLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" id="frmEntry">
        <div class="modal-content">
            <div class="modal-header blue-gradient-rgba">
                <h4 class="modal-title heading lead white-text font-weight-bold"><i class="fas fa-edit"></i> Form Entri Data bencana</h4>
                <button type="button" class="close btnClose" aria-label="Close">
                    <span aria-hidden="true" class="white-text">&times;</span>
                </button>
            </div>
            <?php echo form_open_multipart(site_url(isset($siteUri) ? $siteUri . '/create' : ''), array('id' => 'formEntry', 'class=' => 'needs-validated', 'novalidate' => '')); ?>
            <div class="modal-body">
                <div id="errEntry"></div>
                <?php echo form_hidden('tokenId', ''); ?>

                <div class="form-row mb-3">
                    <label for="jenis_bencana" class="control-label font-weight-bold">Jenis Bencana<span class="text-danger">*</span></label>
                    <div class="col-12 col-md-12">
                        <div class="row">
                            <?php foreach ((isset($jenis_bencana) ? $jenis_bencana : array()) as $key => $dg) {
                                echo '<div class="col-3">';
                                echo '<div class="custom-control custom-checkbox required">';
                                echo '<input type="checkbox" class="custom-control-input level" name="groupid[]" id="groupid_' . $dg['id_jenis_bencana'] . '" value="' . $this->encryption->encrypt($dg['id_jenis_bencana']) . '" ' . set_checkbox('groupid[]', $this->encryption->encrypt($dg['id_jenis_bencana'])) . ' required>';
                                echo '<label class="custom-control-label" for="groupid_' . $dg['id_jenis_bencana'] . '">' . $dg['nm_bencana'] . '</label>';
                                echo '<div class="invalid-feedback"></div>';
                                echo '</div>';
                                echo '</div>';
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="form-row mb-3">
                    <div class="col-12 col-md-4 required">
                        <label for="nama_bencana" class="control-label font-weight-bold"> Nama Bencana <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_bencana" id="nama_bencana" placeholder="Nama Bencana" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12 col-md-4 required">
                        <label for="daterange" class="control-label font-weight-bold">Tanggal Tanggap Bencana<span class="text-danger">*</span></label>
                        <input type="text" placeholder="Select date" class="form-control" id="daterange" name="daterange">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12 col-md-4 required">
                        <label for="jam_bencana" class="control-label font-weight-bold">Jam Bencana <span class="text-danger">*</span></label>
                        <input type="text" class="form-control timepicker" placeholder="Select time" name="jam_bencana" id="jam_bencana" value="<?php echo $this->input->post('jam_bencana', TRUE); ?>" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <h6 class="control-label font-weight-bold">TITIK KOORDINAT LOKASI BENCANA</h6>
                <div id="map-canvas" class="mb-3"></div>
                <div class="form-row mb-3">
                    <div class="col-12 col-md-6 required">
                        <input type="text" class="form-control" name="latitude" id="latitude" placeholder="Latitude" value="<?= $this->input->post('latitude', TRUE); ?>" readonly required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12 col-md-6 required">
                        <input type="text" class="form-control" name="longitude" id="longitude" placeholder="Longitude" value="<?= $this->input->post('longitude', TRUE); ?>" readonly required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="form-row mb-3">
                    <div class="col-12 col-md-12 required">
                        <input type="text" class="form-control" name="address" id="address" placeholder="address" value="<?= $this->input->post('address', TRUE); ?>" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="form-row mb-3">
                    <div class="col-12 col-md-6 required">
                        <label for="nama_file" class="control-label font-weight-bold">Foto Bencana <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="customFile" name="nama_file" id="nama_file" lang="in" value="<?= $this->input->post('nama_file', TRUE); ?>">
                            <label class="custom-file-label" for="nama_file"> </i>Silahkan Pilih File</label>
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-12 col-md-6 required">
                        <label for="fullname" class="control-label font-weight-bold">OPD Penanggung Jawab <span class="text-danger">*</span></label>
                        <?php echo form_multiselect('fullname[]', isset($data_user) ? $data_user : array('' => 'OPD Penanggung Jawab'), $this->input->post('fullname[]', TRUE), 'class="form-control select-all" data-placeholder="Pilih Data"  style="width:100%" required=""'); ?>
                        <div class="invalid-feedback"></div>
                    </div>

                </div>
                <div class="alert alert-danger">
                    Ukuran dokumen yang diupload maksimal 2 Mb. Format dokumen yang diupload harus pdf/ppt</div>
                <div class="blockquote-footer">
                    <span><b>NB:</b> Untuk kolom anggaran dan profil hanya optional</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-blue-grey waves-effect waves-light px-3 py-2 font-weight-bold btnClose"><i class="fas fa-times"></i> Close</button>
                <button type="submit" class="btn btn-primary waves-effect waves-light px-3 py-2 font-weight-bold" name="save" id="save"><i class="fas fa-check"></i> Submit</button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<!------------------------------------ FORM ENTRI DATA BENCANA -------------------------------------------->