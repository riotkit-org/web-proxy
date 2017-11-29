<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy\Service\Security;

use Wolnosciowiec\WebProxy\Entity\ForwardableRequest;

interface AuthCheckerInterface
{
    /**
     * @param ForwardableRequest $request
     * @return bool
     */
    public function isValid(ForwardableRequest $request): bool;

    /**
     * @param ForwardableRequest $request
     * @return bool
     */
    public function canHandle(ForwardableRequest $request): bool;
}
