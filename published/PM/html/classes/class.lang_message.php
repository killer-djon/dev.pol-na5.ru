<?php

class Lang_Message
{
    function Lang_Message()
    {}
    
    function render($data)
    {
        global $pmStrings;
        if(!is_array($data))
        {
            $data = array($data);
        };
        $string = array_shift($data);
        $string = $pmStrings[$string];
        return str_replace(array_map(create_function('$a', 'return "{".$a."}";'), array_keys($data)), array_values($data), $string);
    }
};

?>