<?php

namespace Aspro\Smartseo\Seo;

class SitemapFile extends \Bitrix\Seo\SitemapFile
{
    const ENTRY_TPL = '<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%s</priority></url>';

    public function __construct($fileName, $settings)
    {
        if ($settings['PROTOCOL']) {
            $settings['PROTOCOL'] = str_replace('://', '', $settings['PROTOCOL']);
        }

        parent::__construct($fileName, $settings);
    }

    public function addEntry($entry)
    {
        if ($this->isSplitNeeded()) {
            $this->split();
            $this->addEntry($entry);
        } else {
            if (!$this->partChanged) {
                $this->addHeader();
            }

            $converter = \Bitrix\Main\Text\Converter::getXmlConverter();

            $this->putContents(
                sprintf(
                    self::ENTRY_TPL,
                    $converter->encode($entry['XML_LOC']),
                    $converter->encode($entry['XML_LASTMOD']),
                    $converter->encode($entry['XML_CHANGEFREQ']),
                    $converter->encode($entry['XML_PRIORITY'])
                ),
                self::APPEND
            );
        }
    }

    public function getFilePath()
    {
        return $this->getFileUrl($this);
    }
}