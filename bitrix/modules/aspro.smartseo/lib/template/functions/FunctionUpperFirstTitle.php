<?php

namespace Aspro\Smartseo\Template\Functions;

class FunctionUpperFirstTitle extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
    public function calculate(array $inputParameters)
    {
        $parameters = $this->parametersToArray($inputParameters);

        $text = array_shift($parameters);

        return mb_convert_case($text, MB_CASE_TITLE);
    }
}
