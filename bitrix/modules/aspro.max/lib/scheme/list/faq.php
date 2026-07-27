<?php

namespace Aspro\Max\Scheme\List;

class FAQ extends Content
{
    public function buildSchema(): array
    {
        global $APPLICATION;

        $elements = $this->prepareElements();
        $items = [];

        foreach ($elements as $item) {
            $items[] = [
                "@type" => "Question",
                "name" => $item["NAME"],
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => $item["DESCRIPTION"],
                    "datePublished" => $item["DATE"],
                    "author" => [
                        "@type" => "Organization",
                        "name" => $this->siteName,
                        "url" => $this->siteUrl,
                    ],
                ],
            ];
        }

        return [
            "@context" => "https://schema.org/",
            "@type" => "FAQPage",
            "name" => $APPLICATION->GetTitle(),
            "description" => $APPLICATION->GetProperty("description"),
            "mainEntity" => $items,
        ];
    }
}
