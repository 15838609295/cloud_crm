<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
namespace TencentCloud\Mariadb\V20170312\Models;
use TencentCloud\Common\AbstractModel;

/**
 * @method string getZone() 获取欲新购实例的可用区ID。
 * @method void setZone(string $Zone) 设置欲新购实例的可用区ID。
 * @method integer getNodeCount() 获取实例节点个数，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得。
 * @method void setNodeCount(integer $NodeCount) 设置实例节点个数，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得。
 * @method integer getMemory() 获取内存大小，单位：GB，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得。
 * @method void setMemory(integer $Memory) 设置内存大小，单位：GB，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得。
 * @method integer getStorage() 获取存储空间大小，单位：GB，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得不同内存大小对应的磁盘规格下限和上限。
 * @method void setStorage(integer $Storage) 设置存储空间大小，单位：GB，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得不同内存大小对应的磁盘规格下限和上限。
 * @method integer getPeriod() 获取欲购买的时长，单位：月。
 * @method void setPeriod(integer $Period) 设置欲购买的时长，单位：月。
 * @method integer getCount() 获取欲购买的数量，默认查询购买1个实例的价格。
 * @method void setCount(integer $Count) 设置欲购买的数量，默认查询购买1个实例的价格。
 */

/**
 *DescribePrice请求参数结构体
 */
class DescribePriceRequest extends AbstractModel
{
    /**
     * @var string 欲新购实例的可用区ID。
     */
    public $Zone;

    /**
     * @var integer 实例节点个数，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得。
     */
    public $NodeCount;

    /**
     * @var integer 内存大小，单位：GB，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得。
     */
    public $Memory;

    /**
     * @var integer 存储空间大小，单位：GB，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得不同内存大小对应的磁盘规格下限和上限。
     */
    public $Storage;

    /**
     * @var integer 欲购买的时长，单位：月。
     */
    public $Period;

    /**
     * @var integer 欲购买的数量，默认查询购买1个实例的价格。
     */
    public $Count;
    /**
     * @param string $Zone 欲新购实例的可用区ID。
     * @param integer $NodeCount 实例节点个数，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得。
     * @param integer $Memory 内存大小，单位：GB，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得。
     * @param integer $Storage 存储空间大小，单位：GB，可以通过 DescribeDBInstanceSpecs
 查询实例规格获得不同内存大小对应的磁盘规格下限和上限。
     * @param integer $Period 欲购买的时长，单位：月。
     * @param integer $Count 欲购买的数量，默认查询购买1个实例的价格。
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
        if (array_key_exists("Zone",$param) and $param["Zone"] !== null) {
            $this->Zone = $param["Zone"];
        }

        if (array_key_exists("NodeCount",$param) and $param["NodeCount"] !== null) {
            $this->NodeCount = $param["NodeCount"];
        }

        if (array_key_exists("Memory",$param) and $param["Memory"] !== null) {
            $this->Memory = $param["Memory"];
        }

        if (array_key_exists("Storage",$param) and $param["Storage"] !== null) {
            $this->Storage = $param["Storage"];
        }

        if (array_key_exists("Period",$param) and $param["Period"] !== null) {
            $this->Period = $param["Period"];
        }

        if (array_key_exists("Count",$param) and $param["Count"] !== null) {
            $this->Count = $param["Count"];
        }
    }
}
