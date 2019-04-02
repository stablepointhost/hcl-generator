<?php
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this
// file, You can obtain one at http://mozilla.org/MPL/2.0/.

Namespace Stablepoint;

class HclGenerator
{
    public  $type;
    public  $provider;
    public  $name;
    public  $objectName;
    private $output;
    public function __construct($provider, $name, $type='resource')
    {
        $this->type       = $type;
        $this->provider   = $provider;
        $this->name       = $name;

        $this->output     = $this->type . ' ' . json_encode($this->provider) . ' ' . json_encode($this->name) . ' {' . PHP_EOL;
        $this->objectName = "{$this->provider}.{$this->name}";
    }

    public function renderConfig()
    {
        $this->output .= '}';

        return $this->output;
    }

    public function generateAttribute($attribute, bool $rendered=true)
    {
        $attributeFullName = "{$this->objectName}.$attribute";

        if($rendered == false)
        {
            return $attributeFullName;
        }
        else
        {
            return '${' . $attributeFullName . '}';
        }
    }

    public function addArgumentLine(array $argument)
    {
        $keyName = key($argument);
        $this->output .= '    ' . $keyName . ' = '; 
        if(is_array($argument[$keyName]))
        {
            $this->output .= '[' .  implode(', ', array_map('json_encode', $argument[$keyName])) . ']' . PHP_EOL;
        }
        else
        {
            $this->output .= json_encode($argument[$keyName]) . PHP_EOL;
        }
    }

    public function addArgumentArray($argument)
    {
        foreach($argument as $k => $v)
        {
           if(!is_array($v))
           {
                $this->addArgumentLine([$k=>$v]);
           }
           else
           {
                $this->output .= '    ' . $k . ' {' . PHP_EOL;
                foreach($v as $k2=>$v2)
                {
                    $this->output .= '    ';
                    $this->addArgumentLine([$k2=>$v2]);
                }

                $this->output .= '    }' . PHP_EOL;
           }
        }
    }
} 