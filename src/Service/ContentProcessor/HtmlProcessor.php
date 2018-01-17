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
        'form'   => ['action']
    ];

    /**
     * @var OneTimeTokenUrlGenerator $urlGenerator
     */
    private $urlGenerator;

    /**
     * @var CssProcessor $cssProcessor
     */
    private $cssProcessor;

    /**
     * @param OneTimeTokenUrlGenerator $urlGenerator
     */
    public function __construct(OneTimeTokenUrlGenerator $urlGenerator, CssProcessor $cssProcessor)
    {
        $this->urlGenerator = $urlGenerator;
        $this->cssProcessor = $cssProcessor;
    }

    /**
     * @inheritdoc
     */
    public function process(ForwardableRequest $request, string $input, string $mimeType = null): string
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($input);

        foreach (self::ELEMENTS_MAPPING as $tagName => $attributeNames) {
            foreach ($attributeNames as $attributeName) {
                /**
                 * @var \DOMElement[] $tags
                 */
                $tags = $dom->getElementsByTagName($tagName);

                if (!$tags) {
                    continue;
                }
                
                if ($tagName === 'img') {
                    $this->processScalableImages($request, $tags);
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

        $this->handleStyleTags($request, $dom);

        return $dom->saveHTML();
    }
    
    private function handleStyleTags(ForwardableRequest $request, \DOMDocument $dom)
    {
        /**
         * @var \DOMElement[] $styleTags
         */
        $styleTags = $dom->getElementsByTagName('style');
        
        foreach ($styleTags as $styleTag) {
            $html          = $this->_domNodeToHTML($styleTag);
            $processedHtml = $this->cssProcessor->process($request, $html, 'text/css');
            $this->_domReplaceHTMLContent($styleTag, $processedHtml);
        }
    }

    private function _domNodeToHTML(\DOMElement $element)
    {
        return array_reduce(
            iterator_to_array($element->childNodes),
            function ($carry, \DOMNode $child) {
                return $carry . $child->ownerDocument->saveHTML($child);
            }
        );
    }

    private function _domReplaceHTMLContent(\DOMElement $element, string $newContent)
    {
	    $fragment = $element->ownerDocument->createDocumentFragment();
	    $fragment->appendXML($newContent);

	    while ($element->hasChildNodes()) {
		    $element->removeChild($element->firstChild);
	    }

	    $element->appendChild($fragment);
    }

    /**
     * @url https://developer.mozilla.org/en-US/docs/Learn/HTML/Multimedia_and_embedding/Responsive_images
     *
     * @param ForwardableRequest $request
     * @param \DOMElement[]|\DOMNodeList $domElements
     */
    private function processScalableImages(ForwardableRequest $request, \DOMNodeList $domElements)
    {
        foreach ($domElements as $image) {
            if ($image->hasAttribute('srcset')) {
                $scalableImages = explode(',', $image->getAttribute('srcset'));

                foreach ($scalableImages as $key => $scalableImage) {
                    $scalableImages[$key] = $this->replaceUrlInScalableImageElement($request, $scalableImage);
                }

                $image->setAttribute('srcset', implode(',', $scalableImages));
            }
        }
    }

    /**
     * @param ForwardableRequest $request
     * @param string $element
     *
     * @return string
     */
    private function replaceUrlInScalableImageElement(ForwardableRequest $request, string $element)
    {
        [$url, $size] = explode(' ', trim($element));
        return $this->rewriteRawUrlToProxiedUrl($request, $url) . ' ' . $size;
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
