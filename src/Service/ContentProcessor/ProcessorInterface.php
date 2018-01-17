<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\ContentProcessor;
use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;

/**
 * Takes care of post-processing the response
 * to replace all links pointing to external resources
 * with links pointing through proxy
 */
interface ProcessorInterface
{
    /**
     * Process all links to external resources
     * such as image links, css, js
     *
     * @param ForwardableRequest $request
     * @param string $input
     * @param string|null $mimeType
     *
     * @return string
     */
    public function process(ForwardableRequest $request, string $input, string $mimeType = null): string;

    /**
     * Tells if can process specified content type
     * 
     * @param string $mimeType
     * @return bool
     */
    public function canProcess(string $mimeType): bool;
}
