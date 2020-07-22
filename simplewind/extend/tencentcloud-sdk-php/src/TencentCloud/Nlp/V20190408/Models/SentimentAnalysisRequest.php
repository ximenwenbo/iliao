<?php
/*
 * Copyright (c) 2017-2018 THL A29 Limited, a Tencent company. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace TencentCloud\Nlp\V20190408\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getText() 获取待分析的文本（仅支持UTF-8格式，不超过200字）
 * @method void setText(string $Text) 设置待分析的文本（仅支持UTF-8格式，不超过200字）
 * @method integer getFlag() 获取文本所属类型（默认取4值）：
1、电商
2、APP
3、美食
4、酒店和其他
 * @method void setFlag(integer $Flag) 设置文本所属类型（默认取4值）：
1、电商
2、APP
3、美食
4、酒店和其他
 */

/**
 *SentimentAnalysis请求参数结构体
 */
class SentimentAnalysisRequest extends AbstractModel
{
    /**
     * @var string 待分析的文本（仅支持UTF-8格式，不超过200字）
     */
    public $Text;

    /**
     * @var integer 文本所属类型（默认取4值）：
1、电商
2、APP
3、美食
4、酒店和其他
     */
    public $Flag;
    /**
     * @param string $Text 待分析的文本（仅支持UTF-8格式，不超过200字）
     * @param integer $Flag 文本所属类型（默认取4值）：
1、电商
2、APP
3、美食
4、酒店和其他
     */
    function __construct()
    {

    }
    /**
     * 内部实现，用户禁止调用
     */
    public function deserialize($param)
    {
        if ($param === null) {
            return;
        }
        if (array_key_exists("Text",$param) and $param["Text"] !== null) {
            $this->Text = $param["Text"];
        }

        if (array_key_exists("Flag",$param) and $param["Flag"] !== null) {
            $this->Flag = $param["Flag"];
        }
    }
}
