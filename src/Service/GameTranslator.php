<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Stichoza\GoogleTranslate\GoogleTranslate;

class GameTranslator
{
    private $cache;
    private $translator;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->translator = new GoogleTranslate('fr');
        $this->translator->setSource('en');
    }

    public function translateText(?string $text): ?string
    {
        if (empty($text)) {
            return null;
        }

        try {
            $cacheKey = 'translation_' . md5($text);
            return $this->cache->get($cacheKey, function (ItemInterface $item) use ($text) {
                $item->expiresAfter(86400); // Cache pour 24 heures
                return $this->translator->translate($text);
            });
        } catch (\Exception $e) {
            return $text;
        }
    }
}