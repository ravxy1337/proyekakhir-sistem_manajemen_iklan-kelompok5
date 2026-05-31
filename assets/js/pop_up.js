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
const popupDel = document.getElementById('popupDelete');
const linkHapus = document.getElementById('linkHapusNyata');
function confirmDelete(id) {
    linkHapus.href = 'delete.php?id=' + id;
    popupDel.classList.remove('hidden');
}
function tutupPopupDelete() {
    popupDel.classList.add('hidden');
}

// pop up jika jenis iklan masih dipakai di penyewaan
function tutupPopupGagal() {
    const popupGagal = document.getElementById('popupGagalHapus');
    if (popupGagal) {
        popupGagal.classList.add('hidden');
    }
}

// pop up succes
function tutupPopupSukses() {
    const popup = document.getElementById('popupSukses');
    if (popup) {
        popup.classList.add('hidden');
    }
}