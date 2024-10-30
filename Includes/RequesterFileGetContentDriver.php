<?php

namespace SociallymapConnect\Includes;

use SociallymapConnect\Includes\Exceptions\RequesterException;

class RequesterFileGetContentDriver extends BaseRequesterDriver
{

    protected function initDriverName()
    {
        $this->name = 'FileGetContent';
    }

    /**
     * @param string $url
     * @return boolean|string
     * @throws Exceptions\SmartEnumException
     * @throws \ReflectionException
     */
    public function download($url)
    {
        $filename = pathinfo($url, PATHINFO_BASENAME);

        $tempFolder = plugin_dir_path(__FILE__) . '/../tmp/';
        if (!file_exists($tempFolder) && !mkdir($tempFolder) && !is_dir($tempFolder)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $tempFolder));
        }
        try {
            $tempFilename = $tempFolder . $filename;
            $content = \file_get_contents($url);
            if ($content === false) {
                $errorMessage = 'Download Error - No more informations';
                throw new RequesterException($errorMessage);
            }

            \file_put_contents($tempFilename, $content);

            return $tempFilename;
        } catch (\Exception $exception) {
            Logger::logError($exception->getMessage());
            return false;
        }
    }

    /**
     * @param string $url
     * @return string
     * @throws Exceptions\SmartEnumException
     * @throws \ReflectionException
     */
    public function sendRequest($url)
    {
        try {
            $response = \file_get_contents($url);
        } catch (\Exception $exception) {
            Logger::logError($exception->getMessage());
        }

        return $response;
    }
}
