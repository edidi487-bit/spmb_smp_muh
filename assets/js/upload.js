// AJAX Document Upload Handler with Progress Bar

function initDocumentUpload(inputId, progressContainerId, progressBarId, progressTextId, docType) {
    const fileInput = document.getElementById(inputId);
    if (!fileInput) return;

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        // 1. Client-Side Size Validation (2 MB limit)
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'error',
                title: 'Ukuran File Terlalu Besar',
                text: 'Maksimal ukuran file adalah 2 MB.'
            });
            this.value = ''; // Reset input
            return;
        }

        // 2. Client-Side Extension Validation
        const allowedExtensions = /(\.jpg|\.jpeg|\.png|\.pdf)$/i;
        if (!allowedExtensions.exec(file.name)) {
            Swal.fire({
                icon: 'error',
                title: 'Format File Tidak Valid',
                text: 'Format file yang diperbolehkan hanya JPG, PNG, atau PDF.'
            });
            this.value = '';
            return;
        }

        // Prepare FormData
        const formData = new FormData();
        formData.append('document_file', file);
        formData.append('jenis_dokumen', docType);

        // Show progress bar
        const progressContainer = document.getElementById(progressContainerId);
        const progressBar = document.getElementById(progressBarId);
        const progressText = document.getElementById(progressTextId);

        if (progressContainer) progressContainer.classList.remove('d-none');
        if (progressBar) {
            progressBar.style.width = '0%';
            progressBar.setAttribute('aria-valuenow', '0');
        }
        if (progressText) progressText.innerText = '0%';

        // Initialize AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload.php', true);

        // Update progress bar
        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                if (progressBar) {
                    progressBar.style.width = percentComplete + '%';
                    progressBar.setAttribute('aria-valuenow', percentComplete);
                }
                if (progressText) {
                    progressText.innerText = percentComplete + '%';
                }
            }
        });

        // Response handler
        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            }).then(() => {
                                location.reload(); // Refresh to update status
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message
                            });
                            if (progressContainer) progressContainer.classList.add('d-none');
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memproses respon server.'
                        });
                        if (progressContainer) progressContainer.classList.add('d-none');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Server',
                        text: 'Terjadi kesalahan pada server saat mengupload file.'
                    });
                    if (progressContainer) progressContainer.classList.add('d-none');
                }
            }
        };

        xhr.send(formData);
    });
}
