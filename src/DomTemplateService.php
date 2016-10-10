<?php  namespace Joyce\DomTemplate; 

use ReflectionClass;

class DomTemplateService {

    private $base_cache_key = '';

    private $template_classes_namespace = '';


    public function __construct() {
        $this->base_cache_key = config('domtemplate.base_cache_key');
        $this->template_classes_namespace = config('domtemplate.template_classes_namespace');
    }

    public function getTemplate($template_name,$root,$view=null,$data = [],$minutes = 0) {

        $file_name = config('domtemplate.base_template_files') . $template_name;

        $cache_key = json_encode($data);
        if($view) {
            list($template_class_name,$template_method) = $this->parseViewName($view);
            $reflector = new ReflectionClass($template_class_name);
            $cache_key = $this->base_cache_key . $template_name . '.' . filemtime($file_name) . '.' . filemtime($reflector->getFileName())  . '.'  . $root  . '.'. $cache_key;
        } else {
            $cache_key = $this->base_cache_key . $template_name . '.' . filemtime($file_name) . '.' . $root  . '.'. $cache_key;
        }

        if($cache_key) {

            if(\Cache::has($cache_key)) {
                return \Cache::get($cache_key);
            } else {
                /** @var DomTemplate $result */
                if($view) {
                    list($template_class_name,$template_method) = $this->parseViewName($view);
                    $result =  $this->callTemplateClass($template_class_name, $template_method, $file_name,$root, $data);
                } else {
                    $result =  BladeDOMTemplate::create($file_name,$root);
                }

                if($minutes == 0) {
                    \Cache::forever($cache_key,$result->__toString());
                } else {
                    \Cache::put($cache_key,$result->__toString(),$minutes);
                }
            }
        } else {
            if($view) {
                list($template_class_name,$template_method) = $this->parseViewName($view);
                $result =  $this->callTemplateClass($template_class_name, $template_method, $file_name,$root, $data);
            } else {
                $result =  BladeDOMTemplate::create($file_name,$root);
            }
        }
        return $result;


    }

    public function parseExpression($expression) {
        echo $expression; die();
    }

    protected function parseViewName($view) {
        if(substr($view,0,1) != '\\') {
            $view = $this->template_classes_namespace . '\\' . $view;
        }
        return explode('@',$view);
    }

    private function callTemplateClass($template_class_name, $template_method, $file_name,$root, $data) {
        $template_class=new $template_class_name;
        $template = BladeDOMTemplate::create($file_name,$root);
        $result = $template_class->$template_method($template,$data);
        return $result;
    }

}
 