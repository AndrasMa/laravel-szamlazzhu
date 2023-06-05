<?php

namespace Omisai\Szamlazzhu;

class SimpleXMLExtended extends \SimpleXMLElement
{
    public function addCDataToNode(\SimpleXMLElement $node, string $value = '')
    {
        if ($domElement = dom_import_simplexml($node)) {
            $domOwner = $domElement->ownerDocument;
            $domElement->appendChild($domOwner->createCDATASection($value));
        }
    }

    public function addChildWithCData(string $name = '', string $value = ''): \SimpleXMLElement
    {
        $newChild = parent::addChild($name);
        if (SzamlaAgentUtil::isNotBlank($value)) {
            $this->addCDataToNode($newChild, $value);
        }

        return $newChild;
    }

    public function addCData(string $value = ''): void
    {
        $this->addCDataToNode($this, $value);
    }

    public function extend(\SimpleXMLElement $add)
    {
        if ($add->count() != 0) {
            $new = $this->addChild($add->getName());
        } else {
            $new = $this->addChild($add->getName(), $this->cleanXMLNode($add));
        }

        foreach ($add->attributes() as $a => $b) {
            $new->addAttribute($a, $b);
        }

        if ($add->count() != 0) {
            foreach ($add->children() as $child) {
                $new->extend($child);
            }
        }
    }

    public function cleanXMLNode(\SimpleXMLElement $data): \SimpleXMLElement
    {
        $xmlString = $data->asXML();
        if (strpos($xmlString, '&') !== false) {
            $cleanedXmlString = str_replace('&', '&amp;', $xmlString);
            $data = simplexml_load_string($cleanedXmlString);
        }

        return $data;
    }

    public function remove(): self
    {
        $node = dom_import_simplexml($this);
        $node->parentNode->removeChild($node);

        return $this;
    }

    public function removeChild(\SimpleXMLElement $child): self
    {
        if ($child !== null) {
            $node = dom_import_simplexml($this);
            $child = dom_import_simplexml($child);
            $node->removeChild($child);
        }

        return $this;
    }
}
