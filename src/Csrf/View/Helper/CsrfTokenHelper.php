<?php

declare(strict_types=1);

namespace Itseasy\Csrf\View\Helper;

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfTokenHelper
{
    protected $field_name;
    protected $tokenManager;

    public function __construct(
        string $field_name,
        CsrfTokenManagerInterface $tokenManager
    ) {
        $this->field_name = $field_name;
        $this->tokenManager = $tokenManager;
    }

    public function __invoke(bool $as_meta = false, bool $debug = false): string
    {
        $csrfToken = $this->tokenManager->getToken("");

        if ($as_meta) {
            return sprintf("<meta name=\"csrf-token\" content=\"%s\" />", $csrfToken);
        }

        if ($debug) {
            $element = "<input type=\"text\" readonly=\"readonly\" name=\"%s\" value=\"%s\" />";
        } else {
            $element = "<input type=\"hidden\" name=\"%s\" value=\"%s\" />";
        }

        return sprintf($element, $this->field_name, $csrfToken);
    }
}

