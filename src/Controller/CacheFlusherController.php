<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Controller;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

final class CacheFlusherController
{
    public function __construct(
        private CacheAdapterInterface $cacheAdapter,
        private ChannelRepositoryInterface $channelRepository,
        private RouterInterface $router,
        private Environment $twig
    )
    {
    }

    public function indexAction(Request $request): Response
    {
        $channelCode = $request->query->has('channel')
            ? (string) $request->query->get('channel')
            : null;

        /** @var ChannelInterface|null $channel */
        $channel = $this->findChannelByCodeOrFindFirst($channelCode);

        if (null === $channel) {
            return new RedirectResponse($this->router->generate('sylius_admin_channel_create'));
        }

        $rendered = $this->twig->render('@FiftyDegSyliusCachePlugin/Admin/index.html.twig', [
            'channel' => $channel,
        ]);

        return new Response($rendered);
    }

    public function flushAction(Request $request): JsonResponse
    {
        $success = $this->cacheAdapter->flush();

        $result = [
            "success" => $success,
        ];

        return new JsonResponse($result);
    }

    private function findChannelByCodeOrFindFirst(?string $channelCode): ?ChannelInterface
    {
        if (null !== $channelCode) {
            return $this->channelRepository->findOneByCode($channelCode);
        }

        return $this->channelRepository->findOneBy([]);
    }
}
