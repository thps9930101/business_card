<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    /* 繁體中文 */
    'accepted' => ':attribute必須接受。',
    'active_url' => ':attribute不是一個有效的網址。',
    'after' => ':attribute必須要晚於 :date。',
    'after_or_equal' => ':attribute必須要等於 :date 或更晚。',
    'alpha' => ':attribute只能以字母組成。',
    'alpha_dash' => ':attribute只能以字母、數字及斜線組成。',
    'alpha_num' => ':attribute只能以字母及數字組成。',
    'array' => ':attribute必須為陣列。',
    'before' => ':attribute必須要早於 :date。',
    'before_or_equal' => ':attribute必須要等於 :date 或更早。',
    'between' => [
        'numeric' => ':attribute必須介於 :min 至 :max 之間。',
        'file' => ':attribute必須介於 :min 至 :max KB 之間。',
        'string' => ':attribute必須介於 :min 至 :max 個字元之間。',
        'array' => ':attribute必須有 :min - :max 個元素。',
    ],
    'boolean' => ':attribute必須為布林值。',
    'confirmed' => ':attribute確認欄位的輸入不一致。',
    'date' => ':attribute不是一個有效的日期。',
    'date_equals' => ':attribute必須要等於 :date。',
    'date_format' => ':attribute不符合 :format 的格式。',
    'different' => ':attribute和 :other 必須不同。',
    'digits' => ':attribute必須是 :digits 位數字。',
    'digits_between' => ':attribute必須是介於 :min 和 :max 位數字。',
    'dimensions' => ':attribute圖片尺寸不正確。',
    'distinct' => ':attribute已經存在。',
    'email' => ':attribute必須是有效的電子郵件地址。',
    'ends_with' => ':attribute必須以 :values 結尾',
    'exists' => '已選定的屬性 :attribute 選項無效。',
    'file' => ':attribute必須是一個檔案。',
    'filled' => ':attribute的欄位是必填的。',
    'gt' => [
        'numeric' => ':attribute必須大於 :value。',
        'file' => ':attribute必須大於 :value KB。',
        'string' => ':attribute必須大於 :value 個字元。',
        'array' => ':attribute必須有 :value 個元素以上。',
    ],
    'gte' => [
        'numeric' => ':attribute必須大於或等於 :value。',
        'file' => ':attribute必須大於或等於 :value KB。',
        'string' => ':attribute必須大於或等於 :value 個字元。',
        'array' => ':attribute必須有 :value 個元素或多個。',
    ],
    'image' => ':attribute必須是一張圖片。',
    'in' => '已選定的屬性 :attribute 選項無效。',
    'in_array' => ':attribute沒有在 :other 中。',
    'integer' => ':attribute必須是一個整數。',
    'ip' => ':attribute必須是一個有效的 IP 位址。',
    'ipv4' => ':attribute必須是一個有效的 IPv4 位址。',
    'ipv6' => ':attribute必須是一個有效的 IPv6 位址。',
    'json' => ':attribute必須是正確的 JSON 字串。',
    'lt' => [
        'numeric' => ':attribute必須小於 :value。',
        'file' => ':attribute必須小於 :value KB。',
        'string' => ':attribute必須小於 :value 個字元。',
        'array' => ':attribute必須有 :value 個元素以下。',
    ],
    'lte' => [
        'numeric' => ':attribute必須小於或等於 :value。',
        'file' => ':attribute必須小於或等於 :value KB。',
        'string' => ':attribute必須小於或等於 :value 個字元。',
        'array' => ':attribute必須不超過 :value 個元素。',
    ],
    'max' => [
        'numeric' => ':attribute不能大於 :max。',
        'file' => ':attribute不能大於 :max KB。',
        'string' => ':attribute不能大於 :max 個字元。',
        'array' => ':attribute最多有 :max 個元素。',
    ],
    'mimes' => ':attribute必須為 :values 的檔案。',
    'mimetypes' => ':attribute必須為 :values 的檔案。',
    'min' => [
        'numeric' => ':attribute必須大於等於 :min。',
        'file' => ':attribute大小不能小於 :min KB。',
        'string' => ':attribute至少為 :min 個字元。',
        'array' => ':attribute至少有 :min 個元素。',
    ],
    'not_in' => '已選定的屬性 :attribute 選項無效。',
    'not_regex' => ':attribute的格式錯誤。',
    'numeric' => ':attribute必須為一個數字。',
    'password' => '密碼錯誤',
    'present' => ':attribute必須存在。',
    'regex' => ':attribute格式錯誤。',
    'required' => ':attribute不能為空。',
    'required_if' => '當 :other 為 :value 時 :attribute 不能為空。',
    'required_unless' => '當 :other 不為 :values 時 :attribute 不能為空。',
    'required_with' => '當 :values 出現時 :attribute 不能為空。',
    'required_with_all' => '當 :values 出現時 :attribute 不能為空。',
    'required_without' => '當 :values 留空時 :attribute field 不能為空。',
    'required_without_all' => '當 :values 都不出現時 :attribute 不能為空。',
    'same' => ':attribute和 :other 必須相同。',
    'size' => [
        'numeric' => ':attribute大小必須為 :size。',
        'file' => ':attribute大小必須為 :size KB。',
        'string' => ':attribute必須是 :size 個字元。',
        'array' => ':attribute必須為 :size 個元素。',
    ],
    'starts_with' => ':attribute必須以 :values 開頭',
    'string' => ':attribute必須是一個字串。',
    'timezone' => ':attribute必須是一個正確的時區值。',
    'unique' => ':attribute已經存在。',
    'uploaded' => ':attribute上傳失敗。',
    'url' => ':attribute格式錯誤。',
    'uuid' => ':attribute必須是一個有效的 UUID。',
    'captcha' => '驗證碼錯誤',
    'mobile' => '手機號碼格式錯誤',
    'tel' => '電話號碼格式錯誤',
    'zip' => '郵政編碼格式錯誤',
    'qq' => 'QQ號碼格式錯誤',
    'integer' => '必須為整數',
    'float' => '必須為浮點數',




    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
