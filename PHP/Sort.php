<?php

class Sort
{
    
    /**
     * 冒泡算法
     * @param array $array
     * @return array []
     */
    public function bubbleSort($array)
    {
        $n = count($array) - 1;
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n - $i; $j++) {
                if ($array[$j] > $array[$j + 1]) {
                    list($array[$j], $array[$j + 1]) = [$array[$j + 1], $array[$j]]; 
                }
            }
        }
        return $array;
    }
    
    /**
     * 短冒泡排序
     * @param array $array
     * @return array []
     */
    public function shortBubbleSort($array)
    {
        $flag = TRUE;
        $n = count($array) - 1;
        while ($n > 0 && $flag) {
            $flag = FALSE;
            for ($i = 0; $i < $n; $i++) {
                if ($array[$i] > $array[$i + 1]) {
                    list($array[$i], $array[$i + 1]) = [$array[$i + 1], $array[$i]];
                }
            }
            $n--;
        }
        return $array;
    }
    
    /**
     * 选择排序
     * @param array $array
     * @return array []
     */
    public function selectionSort($array)
    {
        $n = count($array) - 1;
        for ($i = 0; $i < $n; $i++) {
            $flag = 0;
            for ($j = 1; $j < $n - $i; $j++) {
                if ($array[$j] > $array[$flag]) {
                    $flag = $j;
                }
            }
            list($array[$j], $array[$j + 1]) = [$array[$j + 1], $array[$j]];
        }
        return $array;
    }
    
    /**
     * 插入排序
     * @param array $array
     * @return array []
     */
    public function insertionSort($array)
    {
        $n = count($array) - 1;
        for ($i = 1; $i < $n; $i++) {
            $currentvalue = $array[$i];
            $position = $i;
            while ($position > 0 && $array[$position - 1] > $currentvalue) {
                $array[$position] = $array[$position - 1];
                $position--;
            }
            $array[$position] = $currentvalue;
        }
        return $array;
    }
    
    public function shellSort()
    {
        
    }
    
    private function gapInsertionSort()
    {
        
    }
}

