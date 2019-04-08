<?php
// This Source Code Form is subject to the terms of the Mozilla Public
// License, v. 2.0. If a copy of the MPL was not distributed with this
// file, You can obtain one at http://mozilla.org/MPL/2.0/.

Namespace Stablepoint;

class HclGenerator
{
    protected $type;
    protected $provider;
    protected $name;
    protected $objectName;
    protected $config;

    /** 
     * Publicly modifiable output var for if there should be a defined output
     * @var array Associative array in the format of [$outputName=>$generatedAttribute]
     * @uses $this->generateAttribute() to create value of the array key
     */
    public $output;

    public function __construct($provider, $name, $type='resource')
    {
        $this->type       = $type;
        $this->provider   = $provider;
        $this->name       = $name;

        $this->config     = $this->type . ' ' . json_encode($this->provider) . ' ' . json_encode($this->name) . ' {' . PHP_EOL;
        $this->objectName = "{$this->provider}.{$this->name}";
    }

    /**
     * Render and return the completed config snippet
     * @return string Generated HCL Snippet
     */
    public function renderConfig()
    {
        $this->config .= '}' . PHP_EOL;

        // If someone has defined output, add it on.
        if(isset($this->output))
        {
            $this->config .= 'output ' . json_encode($this->output['name']) .' {' . PHP_EOL;
            $this->config .= '    value = ' . json_encode($this->output['value']) . PHP_EOL;
            $this->config .= '}' . PHP_EOL;
        }

        return $this->config;
    }

    public function generateAttribute($attribute, $object=null, bool $rendered=true)
    {
        $object = isset($object) ? $object : $this->objectName;
        $attributeFullName = "$object.$attribute";

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
        $this->config .= '    ' . $keyName . ' = '; 
        if(is_array($argument[$keyName]))
        {
            $this->config .= '[' .  implode(', ', array_map('json_encode', $argument[$keyName])) . ']' . PHP_EOL;
        }
        else
        {
            $this->config .= json_encode($argument[$keyName]) . PHP_EOL;
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
                if(is_int(key($v)))
                {
                    $this->addArgumentLine([$k=>$v]);
                }
                else
                {
                    $this->config .= '    ' . $k . ' {' . PHP_EOL;
                    foreach($v as $k2=>$v2)
                    {
                        $this->config .= '    ';
                        $this->addArgumentLine([$k2=>$v2]);
                    }

                    $this->config .= '    }' . PHP_EOL;
                }
           }
        }
    }
} 
