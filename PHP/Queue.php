<?php

class Queue
{
    // 队列
    private $queue = [];
    
    /**
     * 构造函数 初始化
     * @param array $queue
     */
    public function __construct($queue = [])
    {
        if (is_array($queue)){
            $this->queue = $queue;
        }
    }
    
    /**
     * 添加 队列 项
     * @param unknown $item
     */
    public function enqueue($item)
    {
        array_push($this->queue, $item);
    }
    
    /**
     * 弹出队列项
     * @return mixed
     */
    public function dequeue()
    {
        return array_shift($this->queue);
    }
    
    /**
     * 检查队列是否为空
     * @return unknown
     */
    public function isEmpty()
    {
        return empty($this->queue);
    }
    
    /**
     * 查询队列总长度
     * @return number
     */
    public function size()
    {
        return count($this->queue);
    }
}

