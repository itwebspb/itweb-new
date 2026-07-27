<?php

namespace Aspro\Max;

use CMax as Solution,
    Bitrix\Main\Application,
    Bitrix\Main\IO\File,
    Bitrix\Main\UserConsent\Agreement;

class Validation
{
    public static function isSolutionForm(): bool
    {
        if (defined('ADMIN_SECTION')) {
            return false;
        }

        if (!isset($_REQUEST[static::getFormFieldName()]) && !isset($_REQUEST['order'][static::getFormFieldName()])) {
            return false;
        }

        return true;
    }

    public static function isAgreementsRequired(): bool
    {
        if (Solution::getFrontParametrValue('SHOW_LICENCE') === 'N') {
            return false;
        }

        if (Solution::getFrontParametrValue('LICENCE_TYPE') !== 'BITRIX'){
            return self::checkContent(self::getPathLicense());
        }

        $request = \Bitrix\Main\Context::getCurrent()->getRequest();

        $code = $request->get('form_id') ?: $request->get('type');
        $isForm = $request->get('form_id') !== null;

        $bitrixAgreementID = $code
            ? Solution::getAgreementIdByOption(
                Solution::getAgreementOptionCodeByFormSID(htmlspecialcharsbx($code), SITE_ID, $isForm)
            )
            : Solution::getFrontParametrValue('AGREEMENT');

        return $bitrixAgreementID ? self::checkBitrixContent($bitrixAgreementID) : false;
    }

    public static function checkContent($path): bool
    {
        if(!File::isFileExists($path)){
            return false;
        }

        return strlen(trim(strip_tags(File::getFileContents($path)))) > 0;

    }

    public static function checkBitrixContent(string $id): bool
    {
        $agreement = new Agreement($id);

        $hasLabelText = strlen(trim(strip_tags($agreement->getLabelText()))) > 0;
        $hasText = strlen(trim(strip_tags($agreement->getText()))) > 0;

        return ($hasLabelText && $hasText);
    }

    public static function getPathLicense()
    {
        return str_replace('//', '/', Application::getDocumentRoot().SITE_DIR."include/licenses_text.php");
    }

    public static function isCaptchaOnSubscribeRequired(): bool
    {
        return Solution::getFrontParametrValue('CAPTCHA_ON_SUBSCRIBE') === 'Y';
    }

    public static function getFormField(): string
    {
        return '<input type="hidden" name="'.static::getFormFieldName().'">';
    }

    public static function getFormFieldName(): string
    {
        return Solution::partnerName.'_'.Solution::solutionName.'_form_validate';
    }
}
