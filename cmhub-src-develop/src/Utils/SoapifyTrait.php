<?php

namespace App\Utils;

/**
 * Class SoapifyTrait
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
trait SoapifyTrait
{
    /**
     *
     * @param string $request The request
     *
     * @return string
     */
    public function soapify(string $request): string
    {
        $body = str_replace(
            '<?xml version="1.0" encoding="utf-8"?>',
            '',
            str_replace(
                '<?xml version="1.0" encoding="UTF-8"?>',
                '',
                $request
            )
        );

        return '<?xml version="1.0" encoding="UTF-8"?>' .
            '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:ota = "http://www.opentravel.org/OTA/2003/05">' .
            '<soap:Body>' .
            preg_replace(
                '/<([\/]*)/',
                '<$1ota:',
                $body
            ) .
            '</soap:Body>' .
            '</soap:Envelope>';
    }

    /**
     *
     * @param string $response The response
     *
     * @return string
     */
    public function desoapify(string $response): string
    {
        return str_replace(
            '<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:ns1="http://www.opentravel.org/OTA/2003/05"><env:Body>',
            '',
            str_replace(
                '</env:Body></env:Envelope>',
                '',
                str_replace(
                    'ns1:',
                    '',
                    $response
                )
            )
        );
    }
}
