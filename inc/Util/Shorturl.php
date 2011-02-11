<?php
Class Util_Shorturl {
    
    const SYMBOLS_62 = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    
    public static function fromUuid($uuid, $base=62) {
        $ints = preg_replace("#[A-Z-]#","", $uuid);
        return self::toBase($ints, $base);
    }
    
    
    public static function fromId($id, $base=62) {
        return self::toBase($id, $base);
    }
    
    
    public static function toBase($id, $base) {
       $method = 'toBase' . $base;
       if (method_exists('self', $method)) {
           return call_user_func('self::'.$method, $id, $base);
       }
       return null;
    }
    
    
    public static function toBase62($id) {
        if (empty($id)) {
            return null;
        }
        $short = "";
        $mod = 0;
        $base = 62;
        $symbols = self::SYMBOLS_62;
        while ((int) $id != 0) {
            $mod = (int) ($id % $base);
            $short = $symbols[$mod] . $short;
            $id = $id / $base;
        }
        return $short;
    }
    
    
    public static function fromBase62($in) {
        if (empty($in)) {
            return 0;
        }
        $l = strlen($in);
        $val = 0;
        for($i=0; $i<$l; $i++) {
            $index = strpos(self::SYMBOLS_62, $in[$i]);
            $val += pow(62, ($l - $i -1)) * $index;
        }
        return $val;
    }
    
    
    
}