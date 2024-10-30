<?php

namespace Mocks;

use SociallymapConnect\Includes\BaseRequesterDriver;
use SociallymapConnect\Includes\HttpRequester\HttpResponse;

class RequesterMockDriver extends BaseRequesterDriver
{
    protected function initDriverName()
    {
        $this->name = 'Mock';
    }


    public function sendRequest($url)
    {

        $query = parse_url($url, PHP_URL_QUERY);

        $token = explode('=', $query);

        switch ($token[1]) {
            case 'json':
                $content = file_get_contents(__DIR__ . '/response.json');
                break;
            case 'empty':
                $content = '[]';
                break;
            default:
                throw new \Exception();
        }

        $response = new HttpResponse();
        $response->setHttpStatusCode(200);
        $response->setHttpStatusPhrase('Ok');
        $response->setRawBody($content);

        return $response;
    }

    /**
     * @param string $url
     * @return string
     */
    public function download($url)
    {
        $file = tempnam(sys_get_temp_dir(), '');

        \file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA90lEQVQ4T43TPyuGYRQG8N+Lko8gX4OU8gVsFn8Go2wWKUVRysBusxj822S0YJONwUhGWZQkKTr1vnV7ep7nvs94OtfVda7rnI589WOoMvaFn+h1Mvg+XGAqmXvGGN5KCHaxmoA/MIGHXq9NwQIOE/AvpnGeqm4iGMc1BpPhdexUV64jGMEdhpPhKyxWwC/4rhKE2zcYbTE3EljBfp2JR5hvAT9iDvclJoaa8CEiizrAMj5LTIyZE8ziHUs4rVPWlMImtnDbXempaa06ghkcYw8bvZMtJQj3z7qSL/Nv8v8XIv9trOG1BJzGOIBJxMHEyRZX7huzRH8zEikROsDYQwAAAABJRU5ErkJggg=='));

        return $file;
    }

}
