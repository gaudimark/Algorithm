<?php

/**
 * PHP实现双向队列 双端队列
 * 双端队列 (deque 全名double-ended queue) 是一种具有队列和栈性质的数据结构
 * 双端队列中的元素可以从两端弹出 插入和删除操作限定在队列的两边进行
 */
class Deque {
    // 队列存储
    private $queue = array();
    
    /**
     * 构造函数初始化队列
     */
    public function __construct($queue = array()) {
        if (is_array($queue)) {
            $this->queue = $queue;
        }
    }
    
    /**
     * front
     * 获取第一个元素
     */
    public function front() {
        return reset($this->queue);
    }
    
    /**
     * back 获取第一个元素
     */
    public function back() {
        return end($this->queue);
    }
    
    /**
     * isEmpty 判断是否为空
     */
    public function isEmpty() {
        return empty($this->queue);
    }
    
    /**
     * size 队列大小
     */
    public function size() {
        return count($this->queue);
    }
    
    /**
     * pushBack 插入到尾部
     */
    public function pushBack($val) {
        array_push($this->queue, $val);
    }
    
    /**
     * pushFront 插入到头部
     */
    public function pushFront($val) {
        array_unshift($this->queue, $val);
    }
    
    /**
     * popBack 移除最后一个元素
     */
    public function popBack() {
        array_pop($this->queue);
    }
    
    /**
     * popFront 移除第一个元素
     */
    public function popFront($val) {
        array_shift($this->queue);
    }
    
    /**
     * clear 清空队列
     */
    public function clear() {
        $this->queue = array();
    }
    
}


