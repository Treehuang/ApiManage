<?php
/**
 * Created by IntelliJ IDEA.
 * User: Kinming
 * Date: 2017/12/18
 * Time: 下午6:10.
 */

namespace library;

class DataConst
{
    //string类型的异常数据个数
    const STRING_ERROR_COUNT = 7;
    //number类型的异常数据个数
    const NUMBER_ERROR_COUNT = 5;

    //string类型的异常数据种类
    //该属性值为空字符串的情况
    const STRING_BLANK_CHARACTER_TYPE = 1;
    //该属性值小于最小长度的情况
    const STRING_LT_MIN_TYPE = 2;
    //该属性值大于最大长度的情况
    const STRING_GT_MAX_TYPE = 3;
    //该属性值为中文的情况
    const STRING_CHINESE_TYPE = 4;
    //该属性值包含空格的情况
    const STRING_CONTAIN_BLANK_TYPE = 5;
    //该属性值为数字格式的情况
    const STRING_NUMBER_TYPE = 6;

    //number类型的异常数据种类
    //该属性值为字符串的情况
    const NUMBER_STRING_TYPE = 1;
    //该属性值小于最小值的情况
    const NUMBER_LT_MIN_TYPE = 2;
    //该属性值大于最大值的情况
    const NUMBER_GT_MAX_TYPE = 3;
    //该属性值为浮点数的情况
    const NUMBER_FLOAT_TYPE = 4;
}
