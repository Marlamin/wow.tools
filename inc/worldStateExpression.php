<?php

class worldStateExpression
{
    private $offset = 0;
    private $bytes = [];
    public $state = [];

    function __construct($hexBytes)
    {
        $this->bytes = hex2bin($hexBytes);
        $enabled = unpack("cisEnabled", $this->bytes, $this->offset++)['isEnabled'];
        if ($enabled) {
            $this->state[0] = $this->evalLogicalExp();
        }

        ksort($this->state);
    }

    private function evalLogicalExp()
    {
 // Level 1
        $ret = [];
        $ret['relational'] = $this->evalRelationalExp();
        $ret['op'] = unpack("cOp", $this->bytes, $this->offset++)['Op'];
        $op = $ret['op'];
        $i = 1;
        while ($op != 0) {
            $this->state[$i]['relational'] = $this->evalRelationalExp();
            if ($this->offset >= strlen($this->bytes)) {
                $this->state[$i]['op'] = 0;
            } else {
                $this->state[$i]['op'] = unpack("cOp", $this->bytes, $this->offset++)['Op'];
            }

            $op = $this->state[$i]['op'];
            $i++;
        }
        return $ret;
    }

    private function evalRelationalExp()
    {
 // Level 2
        $ret = [];
        $ret['arethmatic'] = $this->evalArethmaticExp();
        $ret['op'] = unpack("cOp", $this->bytes, $this->offset++)['Op'];
        if ($ret['op'] != 0) {
            $ret['subArethmatic'] = $this->evalArethmaticExp();
        }
        return $ret;
    }

    private function evalArethmaticExp()
    {
 // Level 3
        $ret = [];
        $ret['value'] = $this->evalValue();
        $ret['op'] = unpack("cOp", $this->bytes, $this->offset++)['Op'];
        if ($ret['op'] != 0) {
            $ret['subValue'] = $this->evalValue();
        }
        return $ret;
    }

    private function evalValue()
    {
 // Level 4
        $ret['type'] = unpack("cType", $this->bytes, $this->offset++)['Type'];
        switch ($ret['type']) {
            case 0:
                                                                                                            $ret['value'] = 0;

                break;
            case 1:
            case 2:
                $ret['value'] = unpack("iValue", $this->bytes, $this->offset)['Value'];
                $this->offset += 4;

                break;
            case 3:
                                                                                                        $ret['function'] = unpack("iFunction", $this->bytes, $this->offset)['Function'];
                $this->offset += 4;
                $ret['functionArg1'] = $this->evalValue();
                $ret['functionArg2'] = $this->evalValue();

                break;
            default:
                throw new Exception("Unknown value type: " . $ret['type']);
        }
        return $ret;
    }
}

class humanReadableWorldStateExpression
{
    private $worldStateExpressionMap = [];
    private $logOps = ["none", "and", "or", "xor"];
    private $relOps = ["ID", "=", "≠", "<", "≤", ">", "≥"];
    private $ariOps = ["ID", "+", "-", "*", "/", "%"];
    private $valueTypes = ["0", "value", "world_state", "function"];

    function __construct($worldStateExpressionMap = [])
    {
        $this->worldStateExpressionMap = $worldStateExpressionMap;
    }

    function stateToString($states)
    {
        $str = "";
        foreach ($states as $state) {
            if ($state['op'] != 0) {
                $str .= "(";
                $str .= $this->relationalToString($state['relational']);
                $str .= ") " . $this->logOps[$state['op']] . " ";
            } else {
                $str .= $this->relationalToString($state['relational']);
                $str .= ")";
            }
        }
        return $str;
    }

    private function functionDesc($funcID, $val1, $val2)
    {
        $arg1 = $val1['value'];
        $arg2 = $val2['value'];
        switch ($funcID) {
            case 0:
                return "0";
            case 1:
                return "random(min: " . $arg1 . ", max: " . $arg2 . ")";
            case 2:
                return "now.month()";
            case 3:
                return "now.day()";
            case 4:
                return "now.time_of_day()";
            case 5:
                return "region";
            case 6:
                return "now.imperial_hours()";
            case 7:
                return "difficulty_id_old()";
            case 8:
                return "holiday_start(holiday_id: " . $this->valueToString($val1) . ", duration_id: " . $arg2 . ")";
            case 9:
                return "holiday_left(holiday_id: " . $this->valueToString($val1) . ", duration_id: " . $arg2 . ")";
            case 10:
                return "holiday_active(holiday_id: " . $this->valueToString($val1) . ")";
            case 11:
                return "now()";
            case 12:
                return "week_number()";
            case 15:
                return "difficulty_id()";
            case 16:
                return "warmode_active()";
            case 22:
                if (!empty($this->worldStateExpressionMap)) {
                    if (isset($this->worldStateExpressionMap[$arg1])) {
                        $inlineExp = new worldStateExpression($this->worldStateExpressionMap[$arg1]);
                        return $this->stateToString($inlineExp->state);
                    } else {
                        return "missingInlineExpression(" . $arg1 . ")";
                    }
                } else {
                    return "inlineExpression(" . $arg1 . ")";
                }
            case 23:
                return "keystone_affix()";
            case 28:
                return "keystone_level()";
            case 33:
                return "random(max: " . $arg1 . ", seed: " . $arg2 . ")";
            default:
                                                                               $unkStr = "unknownFunction_" . $funcID . "(";
            // if($arg1 != "none"){
                        $unkStr .= $this->valueToString($val1);
            // }

                    // if($arg2 != "none"){
                        $unkStr .= ", " . $this->valueToString($val2);
            // }

                        $unkStr .= ")";

                return $unkStr;
        }
    }

    private function relationalToString($relational)
    {
        $str = "(";
        $arethmatic = $relational['arethmatic'];
        if ($arethmatic['op'] != 0) {
            $str .= $this->valueToString($arethmatic['value']) . " " . $this->ariOps[$arethmatic['op']] . " " . $this->valueToString($arethmatic['subValue']);
        } else {
            $str .= $this->valueToString($arethmatic['value']);
        }

        if ($relational['op'] != 0) {
            $str .= " " . $this->relOps[$relational['op']] . " ";
            $subArethmatic = $relational['subArethmatic'];
            if ($subArethmatic['op'] != 0) {
                $str .= $this->valueToString($subArethmatic['value']) . " " . $this->ariOps[$subArethmatic['op']] . " " . $this->valueToString($subArethmatic['subValue']);
            } else {
                $str .= $this->valueToString($subArethmatic['value']);
            }
        }
        return $str;
    }

    private function valueToString($value)
    {
        switch ($value['type']) {
            case 0:
                return "0";
            case 1:
                return $value['value'];
            case 2:
                return $this->valueTypes[$value['type']] . "(" . $value['value'] . ")";
            case 3:
                return $this->functionDesc($value['function'], $value['functionArg1'], $value['functionArg2']);
        }
    }
}
