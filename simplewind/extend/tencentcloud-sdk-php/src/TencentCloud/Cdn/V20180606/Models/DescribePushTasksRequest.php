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
namespace TencentCloud\Cdn\V20180606\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getStartTime() 获取开始时间，如2018-08-08 00:00:00。
 * @method void setStartTime(string $StartTime) 设置开始时间，如2018-08-08 00:00:00。
 * @method string getEndTime() 获取结束时间，如2018-08-08 23:59:59。
 * @method void setEndTime(string $EndTime) 设置结束时间，如2018-08-08 23:59:59。
 * @method string getTaskId() 获取提交时返回的任务 Id，查询时 TaskId 和起始时间必须指定一项。
 * @method void setTaskId(string $TaskId) 设置提交时返回的任务 Id，查询时 TaskId 和起始时间必须指定一项。
 * @method string getKeyword() 获取查询关键字，请输入域名或 http(s):// 开头完整 URL。
 * @method void setKeyword(string $Keyword) 设置查询关键字，请输入域名或 http(s):// 开头完整 URL。
 * @method integer getOffset() 获取分页查询偏移量，默认为 0 （第一页）。
 * @method void setOffset(integer $Offset) 设置分页查询偏移量，默认为 0 （第一页）。
 * @method integer getLimit() 获取分页查询限制数目，默认为20。
 * @method void setLimit(integer $Limit) 设置分页查询限制数目，默认为20。
 * @method string getArea() 获取查询刷新记录指定地区。mainland：中国大陆。
 * @method void setArea(string $Area) 设置查询刷新记录指定地区。mainland：中国大陆。
 * @method string getStatus() 获取查询指定任务状态，fail表示失败，done表示成功，process表示刷新中。
 * @method void setStatus(string $Status) 设置查询指定任务状态，fail表示失败，done表示成功，process表示刷新中。
 */

/**
 *DescribePushTasks请求参数结构体
 */
class DescribePushTasksRequest extends AbstractModel
{
    /**
     * @var string 开始时间，如2018-08-08 00:00:00。
     */
    public $StartTime;

    /**
     * @var string 结束时间，如2018-08-08 23:59:59。
     */
    public $EndTime;

    /**
     * @var string 提交时返回的任务 Id，查询时 TaskId 和起始时间必须指定一项。
     */
    public $TaskId;

    /**
     * @var string 查询关键字，请输入域名或 http(s):// 开头完整 URL。
     */
    public $Keyword;

    /**
     * @var integer 分页查询偏移量，默认为 0 （第一页）。
     */
    public $Offset;

    /**
     * @var integer 分页查询限制数目，默认为20。
     */
    public $Limit;

    /**
     * @var string 查询刷新记录指定地区。mainland：中国大陆。
     */
    public $Area;

    /**
     * @var string 查询指定任务状态，fail表示失败，done表示成功，process表示刷新中。
     */
    public $Status;
    /**
     * @param string $StartTime 开始时间，如2018-08-08 00:00:00。
     * @param string $EndTime 结束时间，如2018-08-08 23:59:59。
     * @param string $TaskId 提交时返回的任务 Id，查询时 TaskId 和起始时间必须指定一项。
     * @param string $Keyword 查询关键字，请输入域名或 http(s):// 开头完整 URL。
     * @param integer $Offset 分页查询偏移量，默认为 0 （第一页）。
     * @param integer $Limit 分页查询限制数目，默认为20。
     * @param string $Area 查询刷新记录指定地区。mainland：中国大陆。
     * @param string $Status 查询指定任务状态，fail表示失败，done表示成功，process表示刷新中。
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
        if (array_key_exists("StartTime",$param) and $param["StartTime"] !== null) {
            $this->StartTime = $param["StartTime"];
        }

        if (array_key_exists("EndTime",$param) and $param["EndTime"] !== null) {
            $this->EndTime = $param["EndTime"];
        }

        if (array_key_exists("TaskId",$param) and $param["TaskId"] !== null) {
            $this->TaskId = $param["TaskId"];
        }

        if (array_key_exists("Keyword",$param) and $param["Keyword"] !== null) {
            $this->Keyword = $param["Keyword"];
        }

        if (array_key_exists("Offset",$param) and $param["Offset"] !== null) {
            $this->Offset = $param["Offset"];
        }

        if (array_key_exists("Limit",$param) and $param["Limit"] !== null) {
            $this->Limit = $param["Limit"];
        }

        if (array_key_exists("Area",$param) and $param["Area"] !== null) {
            $this->Area = $param["Area"];
        }

        if (array_key_exists("Status",$param) and $param["Status"] !== null) {
            $this->Status = $param["Status"];
        }
    }
}
