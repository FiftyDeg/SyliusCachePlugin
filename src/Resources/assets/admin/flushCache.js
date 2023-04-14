import jquery from 'jquery';

document.addEventListener('DOMContentLoaded', () => {
    const $flushCacheButton = jquery('.js__flushCacheButton');
    const $flushCacheResponse = jquery('.js__flushCacheResponse');

    const endpoint = $flushCacheButton.data('flush-cache-url');
    let messageTimer = 0;

    const setResponseMessage = (innerHTML) => {
        $flushCacheResponse
            .empty()
            .append(innerHTML)

        clearTimeout(messageTimer);
        messageTimer = setTimeout(() => {
            $flushCacheResponse.empty();
        }, 5000);
    }

    const flushCache = () => {
        $flushCacheButton.addClass('loading');

        setResponseMessage('<p>Clearing Cache...</p>');

        jquery
            .post(endpoint)
            .then((res) => {
                const { success } = res;
                const message = success
                    ? 'Cache cleared successfully!'
                    : 'Something went wrong.';

                setResponseMessage(`<p>${message}</p>`);
            })
            .catch((err) => {
                const message = err.message || 'Something went wrong.';

                setResponseMessage(`<p>${message}</p>`);
            })
            .always(() => {
                $flushCacheButton.removeClass('loading');
            })
    }


    $flushCacheButton.on('click', flushCache);
});
