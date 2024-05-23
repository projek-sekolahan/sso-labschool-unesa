		<div class="row">
            <div class="col-xl-12">
                <div class="card overflow-hidden">
                    <div class="bg-primary bg-soft">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-primary p-3">
                                    <h5 class="text-primary">Selamat Datang Kembali <?=ucwords($description)?></h5>
                                    <p>Aplikasi Administrasi Sekolah</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="avatar-md profile-user-wid mb-4">
                                    <img src="<?=$img_location ? $img_location : base_url().'assets/images/user_icon.png'?>" alt="" class="img-thumbnail rounded-circle">
                                </div>
                                <h5 class="font-size-15 text-truncate"><?=ucwords($nama_lengkap)?></h5>
                                <p class="text-muted mb-0 text-truncate"><?=ucwords($description)?></p>
                            </div>
                            <div class="position-relative">
                                <div class="position-absolute bottom-0 end-0">
                                    <button class="btn btn-primary waves-effect waves-light btn-sm btn-action" data-view="detail" data-action="/api/client/users/profile_pengguna" data-param="<?=$email?>" type="button">
                                        View Profile
                                        <i class="mdi mdi-account-arrow-right ms-1"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
