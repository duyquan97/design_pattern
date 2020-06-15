<?php

namespace App\Utils;

/**
 * Class Obfuscator
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class Obfuscator
{
    public const OBFUSCATED_MESSAGE = 'A silent mouth is sweet to hear';

    public const OBFUSCATED_PARAMS = [
        'Authorization',
        'password',
        'MessagePassword',
    ];

    public const OBFUSCATED_PARAMS_XML = [
        'disabled',
    ];

    /**
     * @param mixed $body
     *
     * @return mixed
     */
    public function obfuscate($body)
    {
        if (is_array($body)) {
            foreach ($body as $key => $item) {
                $body[$key] = $this->obfuscate($item);
            }

            return $body;
        }

        $regex = sprintf('/(%s)(.*)?(%s)/U', implode('|', $this::OBFUSCATED_PARAMS_XML), implode('|', $this::OBFUSCATED_PARAMS_XML));
        $body = preg_replace($regex, '$1' . self::OBFUSCATED_MESSAGE . '$3', $body);
        $regex = sprintf('/(\"|"|.?)(%s)(\"|"|.?)(=|:)(\"|").*(\"|")/Ui', implode('|', $this::OBFUSCATED_PARAMS));

        return preg_replace($regex, '$1$2$3$4"' . self::OBFUSCATED_MESSAGE . '"', $body);
    }
}
