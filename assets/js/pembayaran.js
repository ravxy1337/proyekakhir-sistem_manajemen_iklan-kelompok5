function setNominal(val) {
    document.getElementById('nominal').value = Math.floor(val);
}

function validasiPembayaran() {
    let nominal = document.getElementById('nominal').value;
    let minimalDP = totalTagihan / 2;

    if (statusSewa == 'Pending' && nominal < minimalDP) {
        tampilPopupError('Pembayaran awal (DP) minimal 50% dari total tagihan.');
        return false;
    }

    return true;
}
