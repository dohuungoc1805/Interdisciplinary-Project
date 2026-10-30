(() => {
    const storagePrefix = 'qudena_scroll_position:';
    const currentKey = () => `${storagePrefix}${window.location.pathname}${window.location.search}`;

    if ('scrollRestoration' in window.history) {
        window.history.scrollRestoration = 'manual';
    }

    const savePosition = () => {
        try {
            sessionStorage.setItem(currentKey(), JSON.stringify({
                x: window.scrollX,
                y: window.scrollY,
            }));
        } catch {
            // Một số chế độ riêng tư có thể chặn sessionStorage; bỏ qua khi đó.
        }
    };

    const restorePosition = () => {
        let position;

        try {
            position = JSON.parse(sessionStorage.getItem(currentKey()) || 'null');
        } catch {
            position = null;
        }

        if (!position || (!position.x && !position.y)) return;

        // Chờ nội dung, ảnh và các component động render xong để tránh bị kéo về đầu.
        requestAnimationFrame(() => {
            requestAnimationFrame(() => window.scrollTo(
                Number(position.x) || 0,
                Number(position.y) || 0,
            ));
        });
    };

    window.addEventListener('scroll', savePosition, { passive: true });
    window.addEventListener('pagehide', savePosition);
    window.addEventListener('beforeunload', savePosition);
    window.addEventListener('pageshow', restorePosition);

    // Khôi phục cả lần tải đầu tiên, vì pageshow có thể đã chạy trước khi script đăng ký.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restorePosition, { once: true });
    } else {
        restorePosition();
    }
})();
