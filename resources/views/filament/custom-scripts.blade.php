?>

<script>
// Global function for copying to clipboard from Filament actions
window.copyToClipboard = function(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        return new Promise((resolve, reject) => {
            if (document.execCommand('copy')) {
                resolve();
            } else {
                reject();
            }
            document.body.removeChild(textArea);
        });
    }
};

// Listen for custom copy events
document.addEventListener('livewire:load', function () {
    Livewire.on('copyToClipboard', function (text) {
        copyToClipboard(text).then(function() {
            // Success notification will be handled by Filament
        }).catch(function() {
            console.error('Failed to copy to clipboard');
        });
    });
});
</script>
