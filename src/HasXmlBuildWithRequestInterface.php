<?php

namespace Omisai\Szamlazzhu;

use Omisai\Szamlazzhu\SzamlaAgentRequest;

interface HasXmlBuildWithRequestInterface
{
    /**
     * @throws SzamlaAgentException
     */
    public function buildXmlData(SzamlaAgentRequest $request): array;
}