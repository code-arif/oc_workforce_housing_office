<style>
    /* ── Upload Zone ─────────────────────────────── */
    .custom-upload-zone {
        border: 2px dashed #adb5bd;
        border-radius: 10px;
        padding: 24px 16px;
        text-align: center;
        cursor: pointer;
        background: #f8f9fa;
        transition: border-color 0.2s, background 0.2s;
        user-select: none;
    }

    .custom-upload-zone:hover,
    .custom-upload-zone.dragover {
        border-color: #D9A600;
        background: #fffbea;
    }

    .custom-upload-zone .upload-icon {
        font-size: 2rem;
        line-height: 1;
        margin-bottom: 6px;
    }

    .custom-upload-zone p {
        margin: 0;
        font-size: 14px;
        color: #495057;
    }

    .upload-browse-text {
        color: #D9A600;
        font-weight: 600;
        text-decoration: underline;
        cursor: pointer;
    }

    /* ── Image Thumb ─────────────────────────────── */
    .img-thumb-wrapper {
        position: relative;
        width: 100px;
        height: 75px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        cursor: default;
    }

    .img-thumb-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .img-remove-btn {
        position: absolute;
        top: 3px;
        right: 3px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(220, 53, 69, 0.85);
        color: #fff;
        border: none;
        font-size: 11px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.18s;
        cursor: pointer;
        padding: 0;
        z-index: 5;
    }

    .img-thumb-wrapper:hover .img-remove-btn {
        opacity: 1;
    }

    /* ── Video Preview ───────────────────────────── */
    .video-preview-wrapper {
        border-radius: 8px;
        overflow: hidden;
    }

    .video-remove-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: rgba(220, 53, 69, 0.85);
        color: #fff;
        border: none;
        font-size: 13px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 5;
        opacity: 0;
        transition: opacity 0.18s;
    }

    .video-preview-wrapper:hover .video-remove-btn {
        opacity: 1;
    }
</style>
