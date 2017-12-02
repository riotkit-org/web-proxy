<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\ContentProcessor;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;
use Wolnosciowiec\WebProxy\Service\Security\OneTimeTokenUrlGenerator;

/**
 * Processes HTML - wraps external links with the proxy,
 * so as much as possible is redirected through a proxy
 */
class CssProcessor implements ProcessorInterface
{
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
        $output  = $input;
        $matches = explode('url(', $input);
        array_shift($matches);

        foreach ($matches as $match) {
            $splitToEnding = explode(')', $match);
            $rawUrl = $splitToEnding[0] ?? '';
            $cleanUrl = trim($rawUrl, '" \'');

            // do not support inline links
            if (strpos($cleanUrl, 'data:') === 0) {
                continue;
            }

            $output = str_replace($cleanUrl, $this->urlGenerator->generateUrl($request, $cleanUrl), $output);
        }

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function canProcess(string $mimeType): bool
    {
        return strtolower($mimeType) === 'text/css';
    }
}
