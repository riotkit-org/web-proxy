<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\ContentProcessor;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;

class ContentProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[] $processors
     */
    private $processors;

    /**
     * @param ProcessorInterface[] $processors
     */
    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * @inheritdoc
     */
    public function process(ForwardableRequest $request, string $input, string $mimeType = null): string
    {
        if (!$mimeType) {
            throw new \InvalidArgumentException('$mimeType must be provided to the ContentProcessor');
        }

        foreach ($this->processors as $processor) {
            if ($processor->canProcess($mimeType)) {
                return $processor->process($request, $input);
            }
        }
        
        return '';
    }

    /**
     * @inheritdoc
     */
    public function canProcess(string $mimeType): bool
    {
        $processorsThatCanHandleTheProcessing = array_filter(
            $this->processors,
            function (ProcessorInterface $processor) use ($mimeType) {
                return $processor->canProcess($mimeType);
            }
        );

        return count($processorsThatCanHandleTheProcessing) > 0;
    }
}
