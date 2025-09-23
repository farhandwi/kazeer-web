<?php
// resources/views/filament/custom-styles.blade.php

?>

<style>
.qr-code-preview {
    max-width: 100%;
}

.qr-code-preview img {
    transition: transform 0.2s ease-in-out;
}

.qr-code-preview img:hover {
    transform: scale(1.05);
}

/* Custom badge colors for table status */
.fi-badge-color-success {
    background-color: rgb(34 197 94);
}

.fi-badge-color-warning {
    background-color: rgb(251 146 60);
}

.fi-badge-color-info {
    background-color: rgb(59 130 246);
}

.fi-badge-color-danger {
    background-color: rgb(239 68 68);
}

.fi-badge-color-secondary {
    background-color: rgb(107 114 128);
}

/* Table number styling */
.table-number-cell {
    font-weight: 600;
    font-size: 1.1em;
}

/* QR Code column styling */
.qr-code-column img {
    border-radius: 6px;
    border: 2px solid #e5e7eb;
    transition: all 0.2s ease-in-out;
}

.qr-code-column img:hover {
    border-color: #3b82f6;
    transform: scale(1.1);
}
</style>