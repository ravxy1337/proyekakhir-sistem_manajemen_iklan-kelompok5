//  1. INISIALISASI ELEMEN DOM 
const selJenis = document.getElementById('id_jenis');
const selLokasi = document.getElementById('id_lokasi');
const tglMulai = document.getElementById('tgl_mulai');
const tglSelesai = document.getElementById('tgl_selesai');
const txtTotalHari = document.getElementById('total_hari');
const txtTotalHarga = document.getElementById('total_harga');
//  2. FUNGSI FILTER LOKASI BERDASARKAN JENIS IKLAN 
function filterLokasi() {
    let idJenis = selJenis.value;
    selLokasi.innerHTML = '<option value="">-- Pilih Lokasi --</option>';

    if (idJenis) {
        selLokasi.disabled = false;

        // Memfilter 'semuaLokasi' yang ada di memori global browser
        const filtered = semuaLokasi.filter(l => l.id_jenis == idJenis);

        filtered.forEach(l => {
            const opt = document.createElement('option');
            opt.value = l.id;
            opt.dataset.harga = l.harga_per_hari;
            opt.textContent = l.kode_lokasi + ' - ' + l.nama_lokasi + ' (Rp ' + parseInt(l.harga_per_hari).toLocaleString('id-ID') + '/hari)';
            selLokasi.appendChild(opt);
        });
    } else {
        selLokasi.disabled = true;
    }
    hitungHarga();
}

//  3. FUNGSI HITUNG DURASI & TOTAL HARGA OTOMATIS 
function hitungHarga() {
    if (!tglMulai.value || !tglSelesai.value) {
        txtTotalHari.value = '';
        txtTotalHarga.value = '';
        return;
    }

    const mulai = new Date(tglMulai.value);
    const selesai = new Date(tglSelesai.value);
    // Hitung selisih hari (inklusif hari pertama)
    const diffTime = Math.abs(selesai - mulai);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    txtTotalHari.value = diffDays;

    // Hitung total harga berdasarkan dataset lokasi yang dipilih
    if (selLokasi.value) {
        const selectedOpt = selLokasi.options[selLokasi.selectedIndex];
        const harga = selectedOpt.dataset.harga;
        const total = diffDays * harga;
        txtTotalHarga.value = total.toLocaleString('id-ID');
    } else {
        txtTotalHarga.value = '';
    }
}
