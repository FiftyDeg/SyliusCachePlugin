<?php

declare(strict_types=1);

namespace FiftyDeg\SyliusCachePlugin\Controller;

use FiftyDeg\SyliusCachePlugin\Adapters\CacheAdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class CacheFlusherController
{
    public function __construct(
        private CacheAdapterInterface $cacheAdapter,
        private Environment $twig,
    ) {
    }

    public function indexAction(Request $request): Response
    {
        $rendered = $this->twig->render('@FiftyDegSyliusCachePlugin/Admin/index.html.twig', );

        return new Response($rendered);
    }

    public function flushAction(Request $request): JsonResponse
    {
        $success = $this->cacheAdapter->flush();

        $result = [
            'success' => $success,
        ];

        return new JsonResponse($result);
    }
}
