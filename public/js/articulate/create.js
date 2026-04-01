document.addEventListener('DOMContentLoaded', function () {
    const editorBody = document.getElementById('articulate-body');
    const wordCountDisplay = document.getElementById('word-count-display');
    const metaDescription = document.getElementById('meta-description');
    const metaCountDisplay = document.getElementById('meta-count-display');

    function updateWordCount() {
        const text = editorBody.value.trim();
        const words = text.length > 0 ? text.split(/\s+/).length : 0;
        const minutes = Math.max(1, Math.ceil(words / 200));
        wordCountDisplay.innerText = `${words} kata · ~${minutes} mnt baca`;
    }

    function updateMetaCount() {
        const count = metaDescription.value.length;
        metaCountDisplay.innerText = `${count} / 160`;
        if (count > 160) {
            metaCountDisplay.style.color = 'red';
        } else {
            metaCountDisplay.style.color = '#9ca3af';
        }
    }

    if (editorBody) {
        editorBody.addEventListener('input', updateWordCount);
        updateWordCount();
    }

    if (metaDescription) {
        metaDescription.addEventListener('input', updateMetaCount);
        updateMetaCount();
    }
});
