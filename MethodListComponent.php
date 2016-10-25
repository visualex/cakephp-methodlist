<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\App;
use Cake\Utility\Inflector;
use Cake\Event\Event;

class MethodListComponent extends Component
{

   private $config = [];

   public function initialize(array $config = [])
   {
      $this->config = [
         // type of classes to obtain
         'type' => 'Controller',
         // classes that we do not want in the list
         'exclude' => [
            'App\Controller\PagesController',
            'App\Controller\AppController',
         ],
         // location of the classes
         'location' => APP . 'Controller',
         // match the names of the classes in 'location' path
         'match' => '/^[A-Z].*\.php/',
      ];
      $this->config = array_merge($this->config, $config);
   }

   public function getList()
   {
      $files = new \RegexIterator(new \DirectoryIterator(  $this->config['location'] ), $this->config['match']);

      $result = array();
      foreach ($files as $key => $value) {
         $fullFileName = $value->getPathName();
         $pathInfo = pathinfo($fullFileName);
         $fileName = $value->getFileName();
         $className = str_replace('.' . $pathInfo['extension'], '', $fileName);
         $namespacedClass = \Cake\Core\App::className($className, 'Controller');
         if (in_array($namespacedClass, $this->config['exclude'] )) {
            continue;
         }
         $controllerName = str_replace($this->config['type'], '', $className);
         $result[$className]['name'] = $controllerName;
         $result[$className]['namespaced'] = $namespacedClass;
         $result[$className]['displayName'] = Inflector::humanize(Inflector::underscore($controllerName));
         $result[$className]['actions'] = $this->getActions($namespacedClass);
      }
      return $result;
   }

   private function getActions($controller)
   {
      $methods = get_class_methods($controller);
      $methods = $this->removeParentMethods($controller, $methods);
      return $methods;
   }

   private function removeParentMethods($controller, array $methods)
   {
      $appControllerMethods = get_class_methods(get_parent_class($controller));
      return array_diff($methods, $appControllerMethods);
   }

}
