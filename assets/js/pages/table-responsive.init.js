/*
Template Name: Skote - Admin & Dashboard Template
Author: Themesbrand
Website: https://themesbrand.com/
Contact: themesbrand@gmail.com
File: Table responsive Init Js File
*/

function dataLoad(t,s) {
    if (s[1]=="view") {
        var hasil = parseJwt(t.data);
		hasil = decrypt(hasil,'fromResponse');
		if (s[3]=="menu_pages") {
			// Loop melalui data menu pages yang diterima
			$.each(hasil.menu, function (index,item) {
				options = $('<option>', {
					value: item.id,  // Tentukan nilai dari opsi
					text: item.nama_menu     // Tentukan teks dari opsi
				});
				// Periksa jika opsi harus dipilih (selected option)
				if (hasil.result && hasil.result.length > 0) {
					if (item.menu_groupid === hasil.result[0].menu_groupid) {
						options.prop('selected', true);
					}
				}
				// Tambahkan opsi ke dalam elemen select
				$("#menu_groupid").append(options);
			});
			// Loop melalui data rows yang diterima
			$.each(hasil.result, function (index,item) {
				1 == item.is_child && ($("#is_child").prop("checked", true), $(".select-child").show());
				1 == item.is_execute ? $("#is_execute").prop("checked", true) : $("#is_execute").removeAttr("checked");
				$.each(item, function (a,b) {
					$("#"+a).val(b);
				});
			});
		}
		if (s[3]=="menu_roles") {
			$.each(hasil.roles, function (index,item) {
				options = $('<option>', {
					value: item.id,  // Tentukan nilai dari opsi
					text: item.description     // Tentukan teks dari opsi
				});
				// Periksa jika opsi harus dipilih (selected option)
				if (hasil.result && hasil.result.length > 0) {
					if (item.id === hasil.result[0].groups_id) {
						options.prop('selected', true);
						$('#param').val(item.id);
						$('#roles_users').prop('disabled', true);
					}
				}
				// Tambahkan opsi ke dalam elemen select
				$("#roles_users").append(options);
			});
			
			$.each(hasil.permissions, function (index,item) {
				options = $('<option>', {
					value: item.id,  // Tentukan nilai dari opsi
					text: item.name     // Tentukan teks dari opsi
				});
				// Periksa jika opsi harus dipilih (selected option)
				if (hasil.result && hasil.result.length > 0) {
					var permission_ids = hasil.result[0].permission_id.split(',');
					
					// Iterasi melalui permission_ids untuk memeriksa apakah item.id ada di dalamnya
					permission_ids.forEach(function(permission_id) {
						if (item.id === permission_id) {
							options.prop('selected', true);
						}
					});
				}
				// Tambahkan opsi ke dalam elemen select
				$("#permissions_pages").append(options);
			});
		}
        if (s[3]=="profile_pengguna") {
            $(".username").text(hasil.nama_lengkap);
            $(".jabatan").text((hasil.description==null) ? 'Belum Punya Jabatan':hasil.description);
            $("#foto-profile").attr('src',hasil.img_location);
            // Inisialisasi container untuk komponen Bootstrap di luar loop
			var bootstrapComponents = 
			'<div class="row">' +
				'<div class="col-sm-4"><h6 class="mb-0"> Status </h6></div>' +
					'<div class="col-sm-8 text-secondary"><span class="valadjust">' +
						'<div class="form-check form-switch form-switch-lg mb-3">' +
							'<input class="form-check-input" type="checkbox" id="is_active" name="is_active">' +
							'<label class="form-check-label" for="is_active">isActive</label>' +
						'</div>' +
					'</span></div>' +
			'</div><hr>' +
			'<div class="row d-none" id="actionRow">' +
				'<div class="col-sm-4"><h6 class="mb-0"> Action </h6></div>' +
					'<div class="col-sm-8 text-secondary"><span class="valadjust">' +
						'<button type="button" class="btn btn-outline-warning waves-effect waves-light mb-2 me-2 btn-sm btn-action w-100" id="btn-editProfile" data-view="detail" data-action="/api/client/users/detail_pengguna" data-param="'+hasil.email+'">' +
							'<i class="align-middle mdi mdi-pencil-box font-size-18"></i> <span>Edit</span>' +
						'</button>' +
					'</span></div>' +
			'</div><hr>';
			// Inisialisasi container untuk detail
			var detailContainer = '<div class="detail-container"></div>';
			// Append container ke .detail
			$(".detail").append(detailContainer);
			// Loop untuk menambahkan elemen detail ke dalam container
			$.each(hasil, function (a, b) {
			var c = (a.replace('_', ' ')).replace(/\b\w/g, l => l.toUpperCase());
			var d = (b == null || b == '') ? 'Belum Ada Data' : b;
			if (a == 'nomor_induk' || a == 'email' || a == 'phone') {
				var detail = 
					'<div class="row">' +
						'<div class="col-sm-4"><h6 class="mb-0">' + c + '</h6></div>' +
						'<div class="col-sm-8 text-secondary"><span class="valadjust">' + d + '</span></div>' +
					'</div><hr>';
				// Append elemen detail ke dalam container
				$(".detail-container").append(detail);
			}
			});
			$(".detail").append(bootstrapComponents);
			1 == hasil.active ? $("#is_active").prop("checked", true) : $("#is_active").removeAttr("checked");
			if (hasil.jabatan == 1) {
				$("#actionRow").removeClass('d-none');
			}
        }
        if (s[3]=="detail_pengguna") {
			$.each(hasil.roles, function (index,item) {
				options = $('<option>', {
					value: item.id,  // Tentukan nilai dari opsi
					text: item.description     // Tentukan teks dari opsi
				});
				// Periksa jika opsi harus dipilih (selected option)
				if (hasil.result!==null) {
					if (item.id === hasil.result.group_id) {
						options.prop('selected', true);
					}
				}
				// Tambahkan opsi ke dalam elemen select
				$("#jabatan").append(options);
			});
            $.each(hasil.result, function (a, b) {
                $("#"+a).val(b);
            })
        }
		if (s[3]=="detail_presensi") {
			$(".username").text(hasil.nama_lengkap);
            $(".jabatan").text((hasil.description==null) ? 'Belum Punya Jabatan':hasil.description);
            $("#foto-profile").attr('src',hasil.img_location);
            /* $("#btn-editProfile").data('action','/api/client/users/detail_pengguna');
            $("#btn-editProfile").data('param',hasil.email); */
			$.each(hasil.result, function (a, b) {
				if (b.status_kehadiran=='masuk' || b.status_kehadiran=='pulang') {
					var image = b.foto_presensi;
					var keterangan = (b.keterangan.replace('_',' ')).replace(/\b\w/g, l => l.toUpperCase());
				} else{
					var image = b.foto_surat;
					var keterangan = (b.keterangan_kehadiran.replace('_',' ')).replace(/\b\w/g, l => l.toUpperCase());
				}
				if (keterangan == "Masuk Normal" || keterangan == "Pulang Normal") {
					var bg = 'bg-success';
				} else if (keterangan == "Terlambat Masuk" || keterangan == "Pulang Cepat") {
					var bg = 'bg-danger';
				} else {
					var bg = 'bg-warning';
				}
				detail =
					'<div class="card">' +
						'<div class="card-body">' +
							'<h5 class="card-title text-dark" style="text-align: right;">'+(b.status_kehadiran.replace('_',' ')).replace(/\b\w/g, l => l.toUpperCase())+'</h5>' +
							'<div class="row">' +
								'<div class="col-sm-6">' +
									'<div class="mb-3">' +
										'<img src="'+image+'" alt class="img-thumbnail rounded">' +
									'</div>' +
								'</div>' +
								'<div class="col-sm-6">' +
									'<div class="mb-3">' +
										'<label class="form-label">Nama</label>' +
										'<p><strong>'+(b.nama_lengkap.replace('_',' ')).replace(/\b\w/g, l => l.toUpperCase())+'</strong></p>' +
									'</div>' +
									'<div class="mb-3">' +
										'<label class="form-label">Tanggal</label>' +
										'<p><strong>'+b.tanggal_presensi+'</strong></p>' +
									'</div>' +
									'<div class="mb-3">' +
										'<label class="form-label">Waktu</label>' +
										'<p><strong>'+b.waktu_presensi+' WIB</strong></p>' +
									'</div>' +
									'<div class="mb-3">' +
										'<label class="form-label">Keterangan</label>' +
										'<p class="badge '+bg+'"><strong>'+keterangan+'</strong></p>' +
									'</div>' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>';				
				$(".detail").append(detail);
				// console.log(a,b,c);
				/* (b==null || b=='') ? d='Belum Ada Data':d=b;
				if (a=='nomor_induk' || a=='email' || a=='phone') {
					detail = '<div class="row">'+
					'<div class="col-sm-4"><h6 class="mb-0">'+c+'</h6></div>'+
					'<div class="col-sm-8 text-secondary"><span class="valadjust">'+d+'</span></div></div><hr>';
					$(".detail").append(detail);
				} */
			});
		}
        if (s[3]=="bulan_kalender") {
            $.each(hasil, function (a, b) {
                $("#article-id").val(b.idtab);
                $("#article").val(b.article);
                $(".tiny").html(b.description);
                tinyMCE.activeEditor.setContent(b.description);
            });
        }
        if (s[3]=="layar_kalender") {
            $.each(hasil, function (a, b) {
                $("#layar-id").val(b.idtab);
                if (b.is_active==1) {
                    $("#aktif").prop("checked",true);
                }
            });
        }
    }
    else {
        if (s=="form-login") {
            localStorage.setItem("token",t.data.Tokenjwt);
        }
        if (s=="form-verify") {
            localStorage.setItem("token",t.data.token);
            localStorage.setItem("expired",new Date().getTime()+300);
        }
        if (typeof t.data=="object") {
            swalMsg(t.data.title, t.data.message, t.data.info, t.data.location);
        } else {
            var hasil = parseJwt(t.data);
			hasil = decrypt(hasil,'fromResponse');
            swalMsg(hasil.title, hasil.message, hasil.info, hasil.location);
        }
    }
}

