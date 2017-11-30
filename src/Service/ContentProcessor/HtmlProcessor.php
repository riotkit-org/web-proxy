<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\ContentProcessor;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Service\Security\OneTimeTokenUrlGenerator;

/**
 * Processes HTML - wraps external links with the proxy,
 * so as much as possible is redirected through a proxy
 */
class HtmlProcessor implements ProcessorInterface
{
    const ELEMENTS_MAPPING = [
        'img'    => ['src'],
        'a'      => ['href'],
        'link'   => ['href'],
        'base'   => ['href'],
        'script' => ['src'],
    ];

    /**
     * @var OneTimeTokenUrlGenerator $urlGenerator
     */
    private $urlGenerator;

    /**
     * @param OneTimeTokenUrlGenerator $urlGenerator
     */
    public function __construct(OneTimeTokenUrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritdoc
     */
    public function process(ForwardableRequest $request, string $input, string $mimeType = null): string
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($input);

        /**
         * @var \DOMElement[] $images
         */
        $images = $dom->getElementsByTagName('img');

        foreach ($images as $image) {
            $image->getAttribute('src');
        }

        foreach (self::ELEMENTS_MAPPING as $tagName => $attributeNames) {
            foreach ($attributeNames as $attributeName) {
                /**
                 * @var \DOMElement[] $tags
                 */
                $tags = $dom->getElementsByTagName($tagName);

                if (!$tags) {
                    continue;
                }

                foreach ($tags as $tag) {
                    if ($tag->hasAttribute($attributeName)) {
                        $tag->setAttribute(
                            $attributeName,
                            $this->rewriteRawUrlToProxiedUrl($request, $tag->getAttribute($attributeName))
                        );
                    }
                }
            }
        }

        return $dom->saveHTML();
    }

    /**
     * @param ForwardableRequest $request
     * @param string $url
     *
     * @return string
     */
    private function rewriteRawUrlToProxiedUrl(ForwardableRequest $request, string $url)
    {
        return $this->urlGenerator->generateUrl($request, $url);
    }

    /**
     * @inheritdoc
     */
    public function canProcess(string $mimeType): bool
    {
        return $mimeType === 'text/html';
    }
}
