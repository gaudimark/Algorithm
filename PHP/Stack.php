<?php

class Stack
{
    //栈
    private $stack = [];
    
    /**
     * 构造函数  初始化 栈
     * @param array $stack
     */
    public function __construct($stack = [])
    {
        if (is_array($stack)){
            $this->stack = $stack;
        }
    }
    
    /**
     * 判断是否为空
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->stack);
    }
    
    /**
     * 尾部插入数据
     * @param mixed $value
     */
    public function push($value)
    {
        array_push($this->stack, $value);
    }
    
    /**
     * 弹出尾部数据
     * @return string
     */
    public function pop()
    {
        return array_pop($this->stack);
    }
    
    /**
     * 获取顶部项
     * @return mixed
     */
    public function peek()
    {
        return end($this->stack);
    }
    
    /**
     * 获取栈的项数
     * @return number
     */
    public function size()
    {
        return count($this->stack);
    }
}

