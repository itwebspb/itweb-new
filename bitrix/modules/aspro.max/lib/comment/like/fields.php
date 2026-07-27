<?php

namespace Aspro\Max\Comment\Like;

use CUserTypeEntity;

class Fields
{
    public static function ensure(): void
    {
        static $ensured = false;
        if ($ensured) {
            return;
        }

        self::create('BLOG_COMMENT', 'UF_ASPRO_COM_LIKE', 'integer');
        self::create('BLOG_COMMENT_ID', 'UF_LIKE_ID');
        self::create('BLOG_COMMENT', 'UF_ASPRO_COM_DISLIKE', 'integer');
        self::create('BLOG_COMMENT_ID', 'UF_DISLIKE_ID');

        $ensured = true;
    }

    private static function create(string $entityId, string $fieldName, string $fieldType = 'string'): void
    {
        $exists = CUserTypeEntity::GetList([], ['ENTITY_ID' => $entityId, 'FIELD_NAME' => $fieldName])->Fetch();
        if ($exists) {
            return;
        }

        $ob = new CUserTypeEntity();
        $ob->Add([
            'FIELD_NAME' => $fieldName,
            'ENTITY_ID' => $entityId,
            'USER_TYPE_ID' => $fieldType,
            'XML_ID' => $fieldName,
            'SORT' => 100,
            'MULTIPLE' => 'N',
            'MANDATORY' => 'N',
            'SHOW_FILTER' => 'I',
            'SHOW_IN_LIST' => 'Y',
            'EDIT_IN_LIST' => 'Y',
            'IS_SEARCHABLE' => 'N',
        ]);
    }
}
