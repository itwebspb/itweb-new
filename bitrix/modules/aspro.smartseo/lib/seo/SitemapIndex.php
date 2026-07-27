<?php

namespace Aspro\Smartseo\Seo;

use Bitrix\Main\Text\Converter;

class SitemapIndex extends \Bitrix\Seo\SitemapIndex
{

    const ENTRY_TPL_SEARCH = '<sitemap><loc>%s</loc>';

    public function __construct($fileName, $settings)
    {
        if ($settings['PROTOCOL']) {
            $settings['PROTOCOL'] = str_replace('://', '', $settings['PROTOCOL']);
        }

        parent::__construct($fileName, $settings);
    }

    public function removeEntryByUrl($url)
    {
        $fileName = $this->partFile;
        $pattern = sprintf(self::ENTRY_TPL_SEARCH, $url);

        while ($this->isExists()) {
            $c = $this->getContents();
            $p = strpos($c, $pattern);
            unset($c);

            if ($p !== false) {
                $fd = $this->open('r+');
                $converter = Converter::getXmlConverter();

                fseek($fd, (int)$p);
                fwrite($fd, str_repeat(" ", strlen(sprintf(
                    self::ENTRY_TPL,
                    $converter->encode($url),
                    $converter->encode(date('c'))
                ))));
                fclose($fd);
                break;
            }

            if (!$this->isSplitNeeded()) {
                break;
            }

            $this->part++;
            $fileName = substr($fileName, 0, -strlen(self::FILE_EXT)) . self::FILE_PART_SUFFIX . $this->part . substr($fileName, -strlen(self::FILE_EXT));
            $this->reInit($fileName);
        }

        return $fileName;
    }

    public function validateFileSitemapIndex()
    {
        if (!file_exists($this->pathPhysical)) {
            return true;
        }

        $xml = new \SimpleXMLElement(file_get_contents($this->pathPhysical));

        return $xml->getName() == 'sitemapindex';
    }

    public function getSitemapUrl()
    {
        $e = null;
        $url = $this->settings['PROTOCOL'] . '://' . \CBXPunycode::toASCII($this->settings['DOMAIN'], $e) . $this->getFileUrl($this);

        return $url;
    }

}