// Konfirmasi tambah dan edit jenis iklan
function bukaPopup(idPopup) {
    const popup = document.getElementById(idPopup);
    popup.classList.remove('hidden');
}
function tutupPopup(idPopup) {
    const popup = document.getElementById(idPopup);
    popup.classList.add('hidden');
}
function submitFormNyata(idForm) {
    const form = document.getElementById(idForm);
    form.submit();
}

// validasi hapus jenis iklan
function confirmDelete(id, idPopup) {
    const popupDel = document.getElementById(idPopup);
    const linkHapus = document.getElementById('linkHapusNyata');
    if (linkHapus){
    linkHapus.href = 'delete.php?id=' + id;
    };
    if (popupDel){
    popupDel.classList.remove('hidden');
    };
}
function tutupPopupDelete(idPopup) {
    const popupDel = document.getElementById(idPopup);
    if (popupDel){
    popupDel.classList.add('hidden');
    };   
}

// pop up jika jenis iklan masih dipakai di penyewaan
function tutupPopupGagal(idPopup) {
    const popupGagal = document.getElementById(idPopup);
    if (popupGagal) {
        popupGagal.classList.add('hidden');
    }
}

// pop up succes
function tutupPopupSukses(idPopup) {
    const popup = document.getElementById(idPopup);
    if (popup) {
        popup.classList.add('hidden');
    }
}

// menampilkan foto ditab baru
function showPhoto(url) {
    window.open(url, '_blank');
}

// penyewaan
function batalPesanan(id) {
    const linkBatal = document.getElementById('linkAksiBatal');
    const modal = document.getElementById('popupBatalPenyewaan');
    if (linkBatal) {
        linkBatal.href = 'batal.php?id=' + id;
    }
    if (modal) {
        modal.classList.remove('hidden');
    }
}

// logout
function bukaPopupLogout() {
    const modal = document.getElementById('popupKonfirmasiLogout');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function tutupPopupLogout() {
    const modal = document.getElementById('popupKonfirmasiLogout');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// popup alert error validasi (pembayaran, dll)
function tampilPopupError(pesan) {
    const modal = document.getElementById('popupAlertError');
    const teks  = document.getElementById('pesanAlertError');
    if (modal && teks) {
        teks.textContent = pesan;
        modal.classList.remove('hidden');
    }
}
function tutupPopupError() {
    const modal = document.getElementById('popupAlertError');
    if (modal) {
        modal.classList.add('hidden');
    }
}