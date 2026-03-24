<script>
    // Guard against rare cases where a backdrop/dropdown state sticks after login
    // and blocks pointer interaction on the dashboard.
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape', bubbles: true }));
        }, 120);
    });
</script>
