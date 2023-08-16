<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Processor;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

final class Utilities
{
    public function __construct(
        private ChannelContextInterface $channelContext,
        private LocaleContextInterface $localeContext,
    ) {
    }

    public function hashKey(string $key): string
    {
        $channelCode = $this->channelContext->getChannel()->getCode();
        if (null === $channelCode) {
            $channelCode = '';
        }
        $localeCode = $this->localeContext->getLocaleCode();

        $key = $channelCode . '__' . $localeCode . '__' . $key;

        return hash('md5', $key);
    }
}
