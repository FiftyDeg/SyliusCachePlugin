import jquery from 'jquery';

export default class FlushCache {
    constructor() {
        this.$flushCacheButton = jquery('.js__flushCacheButton');
        this.$flushCacheResponse = jquery('.js__flushCacheResponse');
        this.$flushCacheText = jquery('.flushing-cache');
        this.$clearCacheSuccessfullyText = jquery('.cache-cleared-successfully');
        this.$somethingWentWrongText = jquery('.something-went-wrong');

        this.messageTimer = 0;
    }

    run() {
        const {
            $flushCacheButton
        } = this;

        $flushCacheButton.on('click', this.flushAction.bind(this));
    }

    setResponseMessage(innerHTML, timeout = 5000) {
        const {
            $flushCacheResponse
        } = this;

        $flushCacheResponse
            .empty()
            .append(innerHTML)

        clearTimeout(this.messageTimer);

        if (timeout > 0) {
            this.messageTimer = setTimeout(() => {
                $flushCacheResponse.empty();
            }, timeout);
        } else {
            $flushCacheResponse.empty();
        }
    }

    flushAction() {
        const {
            $flushCacheButton
        } = this;

        const endpoint = $flushCacheButton.data('flush-cache-url');

        $flushCacheButton.addClass('loading');
        $flushCacheButton.prop('disabled', true);

        this.setResponseMessage('<p>' + this.$flushCacheText.html() + '...</p>', 0);

        jquery
            .post(endpoint)
            .then((res) => {
                const { success } = res;
                const message = success
                    ? this.$clearCacheSuccessfullyText.html()
                    : this.$somethingWentWrongText.html()

                this.setResponseMessage(`<p>${message}</p>`);
            })
            .catch((err) => {
                const message = err.message || this.$somethingWentWrongText.html();

                this.setResponseMessage(`<p>${message}</p>`);
            })
            .always(() => {
                $flushCacheButton.removeClass('loading');
                $flushCacheButton.prop('disabled', false);
            })
    }
}

