import FlushCache from './flushCache';

document.addEventListener('DOMContentLoaded', () => {
    const flushCache = new FlushCache();

    flushCache.run();
})
