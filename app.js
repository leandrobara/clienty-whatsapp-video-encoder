document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.upload-form');
    const loader = document.getElementById('loader-overlay');
    const allowedExtensions = ['mp4', 'mov', 'm4v', 'mkv', 'webm', 'avi', 'mpg', 'mpeg'];
    const maxSizeMb = Number(form.dataset.maxMb || '50');
    const maxSizeBytes = maxSizeMb * 1024 * 1024;

    if (!form || !loader) {
        return;
    }

    const showLoader = () => {
        loader.classList.add('active');
        loader.setAttribute('aria-hidden', 'false');
    };

    const hideLoader = () => {
        loader.classList.remove('active');
        loader.setAttribute('aria-hidden', 'true');
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const fileInput = form.querySelector('input[type="file"]');
        if (!fileInput || !fileInput.files.length) {
            return;
        }

        const selectedFile = fileInput.files[0];
        const selectedFileName = selectedFile.name || '';
        const extension = selectedFileName.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(extension)) {
            alert('Solo se permiten archivos de video: MP4, MOV, MKV, WebM, AVI o similares.');
            fileInput.value = '';
            return;
        }

        if (selectedFile.size > maxSizeBytes) {
            alert(`El archivo supera el límite de ${maxSizeMb} MB. Elegí un video más liviano.`);
            fileInput.value = '';
            return;
        }

        showLoader();

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action || window.location.href, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error('La conversión falló.');
            }

            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition') || '';
            const match = disposition.match(/filename="?([^"]+)"?/i);
            const downloadFileName = match ? match[1] : 'output_whatsapp.mp4';

            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = downloadFileName;
            document.body.appendChild(link);
            link.click();
            link.remove();
            setTimeout(() => window.URL.revokeObjectURL(downloadUrl), 2000);
        } catch (error) {
            console.error(error);
            alert('Ocurrió un problema al procesar el video. Intentalo nuevamente.');
        } finally {
            hideLoader();
            form.reset();
        }
    });
});
