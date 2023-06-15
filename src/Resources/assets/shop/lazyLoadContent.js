export default class LazyLoadContent {
    constructor() {
        this.selector = '[data-lazy-load-content-url]:not(.loaded), [data-lazy-load-content-url]:not(.loading)';
        this.loadingContentClassname = 'loading';
        this.loadedContentClassname = 'loaded';

        this.intersectionObserver = null;
        this.intersectionOptions = {
            rootMargin: '150px',
            threshold: 0
        }

        this.mutationObserver = null;
        this.mutationOptions = {
            childList: true,
            subtree: true,
            attributes: false
        };
    }

    run() {
        this.polyfill();

        this.lazyLadableContents = document.querySelectorAll(this.selector);

        try {
            this.intersection();
            this.mutation();
        } catch(err) {
            this.fallback();
        }
    }

    intersection() {
        this.intersectionObserver = new IntersectionObserver(this.intersectionCallback.bind(this), this.intersectionOptions);

        this.lazyLadableContents.forEach((contentWrapper) => {
            this.intersectionObserver.observe(contentWrapper);
        });
    }

    intersectionCallback(entries) {
        const  {
            loadingContentClassname,
            loadedContentClassname
        } = this;

        entries.forEach((entry) => {
            const contentWrapper = entry.target;
            const isLoadingOrLoaded = contentWrapper.classList.contains(loadingContentClassname) || contentWrapper.classList.contains(loadedContentClassname);

            if (isLoadingOrLoaded) {
                this.intersectionObserver.unobserve(contentWrapper);
                return;
            }

            if (entry.isIntersecting) {
                const { lazyLoadContentUrl } = contentWrapper.dataset;

                contentWrapper.classList.add(loadingContentClassname);

                fetch(lazyLoadContentUrl)
                    .then((res) => {
                        res.text().then((data) => {
                            contentWrapper.innerHTML = data;
                            contentWrapper.classList.remove(loadingContentClassname);
                            contentWrapper.classList.add(loadedContentClassname);
                        });
                    });

                this.intersectionObserver.unobserve(contentWrapper);
            }
        });
    }

    mutation() {
        const mutationTarget = document.body;

        this.mutationObserver = new MutationObserver(this.mutationCallback.bind(this));
        this.mutationObserver.observe(mutationTarget, this.mutationOptions);
    }

    mutationCallback(mutationList, observer) {
        const {
            selector
        } =  this;

        mutationList.forEach((mutation) => {
            if (
                mutation.addedNodes &&
                mutation.addedNodes.length
            ) {
                mutation.addedNodes
                    .forEach((item) => {
                        if (!('querySelectorAll' in item)) {
                            return;
                        }

                        if (item.classList.contains(selector)) {
                            this.intersectionObserver.observe(item);
                        }

                        item
                            .querySelectorAll(selector)
                            .forEach((contentWrapper) => {
                                this.intersectionObserver.observe(contentWrapper);
                            });
                    })
            }
        });
    }

    polyfill() {
        if (typeof NodeList !== 'undefined' && NodeList.prototype && !NodeList.prototype.forEach) {
            // Yes, there's really no need for `Object.defineProperty` here
            NodeList.prototype.forEach = Array.prototype.forEach;
        }
    }
}
